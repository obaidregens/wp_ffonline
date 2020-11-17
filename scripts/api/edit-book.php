<?php
function api_edit_book() {
    function term_replace($taxonomy, $term, $parent){
        $exists = term_exists($term,$taxonomy,$parent);
        if ($exists !== null){
            return $exists['term_id'];
        }
        // This reverses the wp_unslash before it happens in wp_insert_term
        // Add Slash to single quotes
        $term = str_replace("\'","\\\'",$term);
        // Add Slash to double quotes
        $term = str_replace('\"','\\\"',$term);
        
        $i = wp_insert_term($term,$taxonomy,array(
            'parent'      => $parent,
        ));
        return $i['term_id'];
    }
    required_login();
    required_params(
        'book_id',
        'title',
        'description',
        'reviews',
        'publish',
        'anonymous_reviews'
    );
    $d = &$_POST['data'];
    $e = new err;

    $publish = $d['publish'] === true;
    $reviews = $d['reviews'] === true;
    $anon_review = $d['anonymous_reviews'] === true;    

    if (trim($d['title']) === '') {
        return [
            'code'      => 14,
            'errors'    => (new err)->add("title","Title is required")->array(),
        ];
    }
    if ($d['book_id'] === 'new'){
        $d['book_id'] = wp_insert_post([
            'post_type'         => 'book',
            'post_status'       => 'draft',
            'post_title'        => $d['title']
        ]);
    }
    $book = story::get($d['book_id'],true,false);
    if ( !$book ){
        return [
            'code'      =>  9,
            'errors'    => (new err)->add("title","An error occured")->array(),
        ];
    }
    if (trim($d['description']) === '') {
        $e->add("description","Story summary is required");
    }
    $import_status = import_stories::get_status($book->ID);
    $chapter_disabled = in_array($import_status,["live","reimport"]);
    $old_chapters = published_chapters($book->ID,-1);
    if (!$chapter_disabled) {
        // Chapter
        $new_chapter_ids = array_map('strval',array_column($d['chapters'] ?? [],'ID'));
        $old_chapter_titles = array_column($old_chapters,'post_title','ID');
        $old_chapter_ids = array_map('strval',array_column($old_chapters,'ID'));
        $to_remove = array_diff($old_chapter_ids,$new_chapter_ids);
        $old_chapters_lookup = array_flip($old_chapter_ids);
        $chapter_ids_order = [];
        foreach ( ($d['chapters'] ?? []) as $k => $chapter ) {
            if (
                $chapter['draft_id'] ||
                ($chapter['title'] ?? "") !== ($old_chapter_titles[$chapter['ID']] ?? "") ||
                $chapter['preAN'] !== get_post_meta($chapter['ID'],'pre_author_note',true) ||
                $chapter['postAN'] !== get_post_meta($chapter['ID'],'pre_author_note',true)
            ){
                if (trim($chapter['title'] ?? "") === "") {
                    continue;
                }
                $chapter_id = draft_chapters::save(
                    ($chapter['draft_id'] ?? null) ?: null,
                    $book->ID,
                    substr( ($chapter['title'] ?? "") ,0,80),
                    [ 'pre' => $chapter['preAN'], 'post' => $chapter['postAN'] ],
                    $chapter['ID'] ?? 'new'
                );
                if ( $chapter_id !== false ) {
                    $chapter_ids_order[] = $chapter_id;
                }
                continue;
            }
            if (! isset($old_chapters_lookup[strval($chapter['ID'])]) ) {
                continue;
            }
            $chapter_ids_order[] = $chapter['ID'];
        }
        if (empty($chapter_ids_order)) {
            $e->add("chapters","Select a chapter to publish");
        }
        foreach ($to_remove as $chapter_id ) {
            wp_delete_post( $chapter_id, true );
        }
        $words_total = 0;
        foreach ($chapter_ids_order as $k => $chapter_id) {
            $nu = $k+1;
            $words = intval(get_post_meta( $chapter_id, 'word-count', true ));
            if ($words < 10) {
                $e->add("chapter-$nu","Chapter $nu must be of more than 10 words");
            }
            $words_total += $words;
            update_post_meta( $chapter_id, 'chapter_order', $nu );
        }
        update_post_meta( $book->ID, 'word-count', $words_total );
    }
    else if ($import_status === "live") {
        $chapters = array_combine(array_column($d['chapters'],'ID'),$d['chapters']);
        foreach ($old_chapters as $old_chapter) {
            $chapter = &$chapters[$old_chapter->ID];
            if (!isset($chapter)) {
                continue;
            }
            if (trim($chapter['title'] ?? "") === "") {
                continue;
            }
            if (
                $chapter['draft_id'] ||
                ($chapter['title'] ?? "") !== ($old_chapter->post_title ?? "") ||
                $chapter['preAN'] !== get_post_meta($old_chapter->ID,'pre_author_note',true) ||
                $chapter['postAN'] !== get_post_meta($old_chapter->ID,'pre_author_note',true)
            ){
                $chapter_id = draft_chapters::save(
                    ($chapter['draft_id'] ?? null) ?: null,
                    $book->ID,
                    substr( ($chapter['title'] ?? "") ,0,80),
                    [ 'pre' => $chapter['preAN'], 'post' => $chapter['postAN'] ],
                    $old_chapter->ID
                );
            }
        }
    }

    // Tags
    // Simple Tags
    $simple_tags = [
        'status'    => [true],
        'fandom'    => [true,3],
        'genre'     => [false,3],
        'rating'    => [true],
        'language'  => [true]
    ];
    $selectedFandomIds = [];
    foreach ($simple_tags as $tagName => $bnc) {
        $required = $bnc[0];
        $maxSelect = $bnc[1] ?? 1;
        if ( empty($d[$tagName]) && $required) {
            $e->add($tagName, ucfirst($tagName) . " is required.");
        }
        $tagIds = [];
        foreach ( (empty($d[$tagName]) ? [] : array_slice($d[$tagName],0,$maxSelect) ) as $key => $tagObj) {
            $tagIds[] = intval($tagObj['value']);
        }
        $added = wp_set_post_terms( $d['book_id'], $tagIds, $tagName === 'fandom' ? 'category' : $tagName );
        if ( $tagName === 'fandom' ) {
            $selectedFandomIds = array_map('intval',$added);
        }
    }

    // Characters
    $max_characters=6;
    $charIds = [];
    $charNames = [];
    foreach ( (empty($d['characters']) ? [] : array_slice($d['characters'],0,$max_characters) ) as $k => $character_obj) {
        $f = intval($character_obj['fandom']);
        if ($f !== 0) {
            if ( !in_array($f,$selectedFandomIds) ) {
                continue;
            }
            if (trim($character_obj['label']) === "") {continue;}
            if (strlen($character_obj['label']) > 100) {continue;}
            $char_id = intval(term_replace( 'character', $character_obj['label'], $f ));
        }
        else {
            $exists = term_exists($character_obj['label'],'character',$f );
            if ($exists === null) {continue;}
            $char_id = intval($exists['term_id']);
        }
        $charNames[$f . '>>>' . $character_obj['label']] = $char_id;
        $charIds[] = $char_id;
    }
    wp_set_post_terms( $d['book_id'], $charIds, 'character' );

    // Pairing
    $max_pairings=3;
    $pairings = [];
    $d['pairing'] = (empty($d['pairing']) ? [] : array_slice($d['pairing'],0,$max_pairings) );
    foreach ($d['pairing'] as $k => $pairing_chars) {
        $error = count($pairing_chars) < 2 || count($pairing_chars) > 4;
        if ($error) {
            continue;
        }
        $pairing_charIds = [];
        foreach ($pairing_chars as $pairingChar) {
            $pCharId = $charNames[intval($pairingChar['fandom']) . '>>>' . $pairingChar['label']] ?? null;
            if ($pCharId === null) {continue 2;}
            $pairing_charIds[] = intval($pCharId);
        }
        $pairings[] = $pairing_charIds;
    }
    pairing::set($d['book_id'],$pairings);

    // Tag
    $max_tags=5;
    $tag = [];
    foreach ( (empty($d['tag']) ? [] : array_slice($d['tag'],0,$max_tags) ) as $k => $tag_obj) {
        if (trim($tag_obj['label']) === "") {continue;}
        if (strlen($tag_obj['label']) > 100) {continue;}
        $tag_id = intval(term_replace( 'tag', $tag_obj['label'], 0 ));
        $tag[] = $tag_id;
    }
    wp_set_post_terms( $d['book_id'], $tag, 'tag' );

    // Update

    $is_publish = ((!$e->has()) && $publish);
    $update_arg = [
        'post_title'        => substr($d['title'],0,80),
        'post_excerpt'      => substr($d['description'],0,400),
        'comment_status'    => $reviews ? 'open' : 'closed',
        'post_status'       => $is_publish ? 'publish' : 'draft' 
    ];

    $new_publish = $is_publish && $book->post_status === 'draft';
    $first_publish = get_post_meta( $book->ID, "first_publish", true );
    $t = time();
    if ($new_publish && $first_publish === "") {
        update_post_meta( $book->ID, 'first_publish', $t );
        $update_arg['post_modified'] = current_time( 'mysql' );
        $update_arg['post_modified_gmt'] = current_time( 'mysql', 1 );
    }
    update_post_meta( $book->ID, 'last_edited', $t );

    global $wpdb;
    $wpdb->update(
        'wp_posts',
        $update_arg,
        [
            'ID'                =>  $d['book_id']
        ]
    );
    // Anonymous Reviews
    update_post_meta( $d['book_id'], 'anon_review', $anon_review ? 'true' : 'false' );

    wp_cache_flush();
    return [
        'code'          => 1,
        'selected'      => get_data($d['book_id'])['selected'],
        'errors'        => $publish ? $e->array() : [],
        'new_publish'   => $new_publish
    ];
}
function api_create_fandom() {
    required_login();
    required_params('category','fandom');
    $d = &$_POST['data'];
    $term = get_term($d['category'],'category');
    if (is_wp_error( $term ) || !$term || intval($term->parent) !== 0) {
        return ['code' => 9];
    }
    $fandom = substr(trim($d['fandom']),0,100);
    if ( $fandom === "") {
        return ['code' => 10];
    }
    $terms = get_terms([
        'taxonomy'  => 'category',
        'parent'    => 0,
        'hide_empty'=> false
    ]);
    $fandoms = get_terms([
        'taxonomy'      => 'category',
        'exclude'       => array_column($terms,'term_id'),
        'hide_empty'    => false,
        'fields'        => 'names'
    ]);
    $fs = str_replace(['_',' ','?','+','!','@','#','$','%'],'-',strtolower($fandom));
    foreach ($fandoms as $f ) {
        $ff = str_replace(['_',' ','?','+','!','@','#','$','%'],'-',strtolower($f));
        if ($fs === $ff) {
            return ['code' => 11];
        }
    }
    $term_id = wp_insert_term( $fandom, 'category', [
        'parent'    => $d['category']
    ] )['term_id'];
    update_term_meta( intval($term_id), 'creator', get_current_user_id() );
    return ['code'  => 1];
}
function api_get_book_data() {
    required_login('book_id');
    required_params();
    wp_cache_flush();
    if ($_POST['data']['book_id'] === "new") {
        return [
            'code'   => 1,
            'data'   => get_data("new")
        ];
    }
    $book = story::get($_POST['data']['book_id'],true,false);
    if ( !$book ){
        return ['code'=>9];
    }
   return [
       'code'   => 1,
       'data'   => get_data($book->ID)
    ];
}
function api_edit_chapter() {
    required_login();
    required_params('chapter_id');
    $c = $_POST['data']['chapter_id'];
    $d = draft_chapters::edit($c);
    if ($d === false) {
        return ['code'=>7];
    }
    return ['code'=>1,'draft_id'=>$d];
}