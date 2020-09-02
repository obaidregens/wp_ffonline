<?php
function api_edit_book() {
    if ($d['publish'] === "true") {
        return ['code' => 980];
    }
    function term_replace($taxonomy, $term, $parent){
        $exists = term_exists($term,$taxonomy,$parent);
        if ($exists !== null){
            return $exists['term_id'];
        }
        return wp_insert_term($term,$taxonomy,array(
            'parent'      => $parent,
        ));
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
        !$book || $book->post_type !== 'book'
        || intval($book->post_author) !== intval(get_current_user_id())
    ){
        return ['code'=>9];
    }
    if (trim($d['description']) === '') {
        $publish = false;
    }

    // Chapter
    $new_chapter_ids = array_map('strval',array_column($d['chapters'] ?? [],'ID'));
    $old_chapter_ids =  array_map('strval',published_chapters($book->ID,-1,'ids'));
    $to_remove = array_diff($old_chapter_ids,$new_chapter_ids);
    $old_chapters_lookup = array_flip($old_chapter_ids);
    $chapter_ids_order = [];
    foreach (($d['chapters'] ?? []) as $chapter ) {
        if ($chapter['draft_id']) {
            $chapter_id = draft_chapters::save($chapter['draft_id'],$book->ID,$chapter['title']);
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
    global $wpdb;
    foreach ($to_remove as $chapter_id ) {
        delete_post_meta( $chapter_id, 'chapter_order' );
        $wpdb->update(
            'wp_posts',
            [
                'post_status'   => 'trash'
            ],
            [
                'ID'            => $chapter_id
            ]
        );
    }
    foreach ($chapter_ids_order as $k => $chapter_id) {
        update_post_meta( $chapter_id, 'chapter_order', $k+1 );
    }

    // Tags
    // Simple Tags
    $simple_tags = [
        'status'    => true,
        'fandom'    => true,
        'genre'     => false,
        'rating'    => true,
        'language'  => true
    ];
    foreach ($simple_tags as $tagName => $required) {
        if ( empty($d[$tagName]) && $required) {
            $publish = false;
            continue;
        }
        $tagIds = [];
        foreach ((empty($d[$tagName]) ? [] : $d[$tagName]) as $key => $tagObj) {
            $tagIds[] = intval($tagObj['value']);
        }
        wp_set_post_terms( $d['book_id'], $tagIds, $tagName === 'fandom' ? 'category' : $tagName );
    }

    // Characters
    $charIds = [];
    $charNames = [];
    foreach ( (empty($d['characters']) ? [] : $d['characters']) as $k => $character_obj) {
        $f = intval($character_obj['fandom']);
        $char_id = intval(term_replace( 'character', $character_obj['label'], $f ));
        $charNames[] = $character_obj['label'];
        $charIds[] = $char_id;
    }
    wp_set_post_terms( $d['book_id'], $charIds, 'character' );
    

    // Pairing
    $pairings = [];
    $d['pairing'] = (empty($d['pairing']) ? [] : array_slice($d['pairing'],0,3) );
    foreach ($d['pairing'] as $k => $pairing_chars) {
        $pairing_charNames = array_column($pairing_chars,'label');
        $error = ! empty(array_diff($pairing_charNames,$charNames)) || count($pairing_charNames) < 2 || count($pairing_charNames) > 4;
        if ($error) {
            continue;
        }
        sort($pairing_charNames);
        $pairings[] = implode('/',$pairing_charNames);
    }
    wp_set_object_terms( $d['book_id'], $pairings, 'pairing' );
    // Tag
    $tag = [];
    foreach ( (empty($d['tag']) ? [] : $d['tag']) as $k => $tag_obj) {
        $tag_id = intval(term_replace( 'tag', $tag_obj['label'], 0 ));
        $tag[] = $tag_id;
    }
    wp_set_post_terms( $d['book_id'], $tag, 'tag' );

    // Update Book

    $wpdb->update(
        'wp_posts',
        [
            'post_title'        => substr($d['title'],0,80),
            'post_excerpt'      => substr($d['description'],0,400),
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
    return ['code'=>$success ?? 1,'selected'=>get_data($d['book_id'])['selected']];
}