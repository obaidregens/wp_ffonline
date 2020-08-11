<?php
function api_edit_book() {
    function term_replace($taxonomy, $term, $parent){
        $exists = term_exists($term,$taxonomy,$parent);
        if ($exists !== null){
            return $exists['term_id'];
        }
        return wp_insert_term($term,$taxonomy,array(
            'parent'      => $parent,
            )
        );
    }
    required_login();
    required_params(
        'title','status','reviews','rating','publish','language',
        'fandom','description','book_id','anonymous_reviews'
    );
    $d = &$_POST['data'];
    $book = get_post($d['book_id']);
    if (!$book || $book->post_type !== 'book' || intval($book->post_author) !== intval(get_current_user_id())){
        return_code(9);
    }
    function return_code($code) {
        $d = &$_POST['data'];
        echo json_encode([
            'code'      => $code,
            'book_id'   => $d['book_id']
        ]);
        exit();
    }
    $publish = $d['publish'] === "true";
    $reviews = $d['reviews'] === "true";
    $anon_review = $d['anonymous_reviews'] === "true";    

    global $wpdb;
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
    foreach ((empty($d['pairing']) ? [] : $d['pairing']) as $k => $pairing_chars) {
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
    return_code(1);
}