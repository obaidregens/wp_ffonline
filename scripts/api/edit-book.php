<?php
function api_edit_book() {
    function term_replace($taxonomy, $term, $parent){
        $exists = term_exists($term,$taxonomy,$parent);
        if ($exists !== null){
            return $exists['term_id'];
        }
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

    $publish = $d['publish'] === "true";
    $reviews = $d['reviews'] === "true";
    $anon_review = $d['anonymous_reviews'] === "true";    

    if (trim($d['title']) === '') {
        return ['code'=>14];
    }
    if ($d['book_id'] === 'new'){
        $d['book_id'] = wp_insert_post([
            'post_type'         => 'book',
            'post_status'       => 'draft',
            'post_title'        => $d['title']
        ]);
    }
    $book = get_post($d['book_id']);
    if (
        !$book
        || $book->post_type !== 'book'
        || !is_current_user($book->post_author)
    ){
        return ['code'=>9];
    }
    if (trim($d['description']) === '') {
        $publish = false;
    }

    // Chapter
    $new_chapter_ids = array_map('strval',array_column($d['chapters'] ?? [],'ID'));
    $old_chapter_ids = array_map('strval',published_chapters($book->ID,-1,'ids'));
    $to_remove = array_diff($old_chapter_ids,$new_chapter_ids);
    $old_chapters_lookup = array_flip($old_chapter_ids);
    $chapter_ids_order = [];
    foreach (($d['chapters'] ?? []) as $chapter ) {
        if ($chapter['draft_id']) {
            $chapter_id = draft_chapters::save($chapter['draft_id'],$book->ID,(substr($chapter['title'],0,80)));
            if ($chapter_id !== false) {
                $chapter_ids_order[] = $chapter_id;
            }
            continue;
        }
        if (! isset($old_chapters_lookup[$chapter['ID']])) {
            continue;
        }
        $chapter_ids_order[] = $chapter['ID'];
    }
    if (empty($chapter_ids_order)) {
        if ($d['publish'] === 'true') {
            $success = 2;
        }
        $publish = false;
    }
    foreach ($to_remove as $chapter_id ) {
        wp_delete_post( $chapter_id, true );
    }
    foreach ($chapter_ids_order as $k => $chapter_id) {
        update_post_meta( $chapter_id, 'chapter_order', $k+1 );
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
            $publish = false;
            continue;
        }
        $tagIds = [];
        foreach ( (empty($d[$tagName]) ? [] : array_slice($d[$tagName],0,$maxSelect) ) as $key => $tagObj) {
            $tagIds[] = intval($tagObj['value']);
        }
        if ( $tagName === 'fandom' ) {
            $selectedFandomIds = $tagIds;
        }
        wp_set_post_terms( $d['book_id'], $tagIds, $tagName === 'fandom' ? 'category' : $tagName );
    }

    // Characters
    $max_characters=6;
    $charIds = [];
    $charNames = [];
    foreach ( (empty($d['characters']) ? [] : array_slice($d['characters'],0,$max_characters) ) as $k => $character_obj) {
        $f = intval($character_obj['fandom']);
        if ( !in_array($f,$selectedFandomIds) ) {
            continue;
        }
        $char_id = intval(term_replace( 'character', $character_obj['label'], $f ));
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
        $tag_id = intval(term_replace( 'tag', $tag_obj['label'], 0 ));
        $tag[] = $tag_id;
    }
    wp_set_post_terms( $d['book_id'], $tag, 'tag' );

    // Update Book
    global $wpdb;
    $wpdb->update(
        'wp_posts',
        [
            'post_title'        => htmlspecialchars(substr($d['title'],0,80)),
            'post_excerpt'      => htmlspecialchars(substr($d['description'],0,400)),
            'comment_status'    => $reviews ? 'open' : 'closed',
            'post_status'       => $publish ? 'publish' : 'draft' 
        ],
        [
            'ID'                =>  $d['book_id']
        ]
    );
    // Anonymous Reviews
    update_post_meta( $d['book_id'], 'anon_review', $anon_review ? 'true' : 'false' );

    if ($d['publish'] === "true" && $publish === false) {
        $success = $success ?? 3;
    }
    wp_cache_flush();
    return [
        'code'      => $success ?? 1,
        'selected'  => get_data($d['book_id'])['selected']
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
    $book = get_post($_POST['data']['book_id']);
    if (
        !$book ||
        $book->post_type !== 'book' ||
        !is_current_user($book->post_author)
    ){
        return ['code'=>9];
    }
   return [
       'code'   => 1,
       'data'   => get_data($book->ID)
    ];
}