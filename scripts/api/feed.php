<?php
function api_save_feed_settings () {
    required_login();
    required_params('fandom','rating','language');
    $d = &$_POST['data'];

    $ids = (new book_query(['page'=>1]))->ids;
    $ratings = (new tag_query('rating',$ids))->get();
    
    foreach (['fandom','rating','language'] as $tagName) {
        $term_ids = (new tag_query($tagName,$ids))->get_ids();
        $d[$tagName] = array_unique($d[$tagName]);
        foreach ($d[$tagName] as $k => $term_id) {
            if (!in_array($term_id,$term_ids)) {
                return ['code'=>8];
            }
        }
        array_splice($d[$tagName],30);
    }
    feed::set_settings([
        "fandom"    => $d['fandom'],
        "rating"    => $d['rating'],
        "language"  => $d['language']
    ]);
    return ['code'=>1];
}
function api_get_feed_settings () {
    required_login();

    $all_ids = (new book_query(['per_page'=>1]))->ids;
    $settings = feed::get_settings();
    foreach ($settings as $tag_name => $ids) {
        $tags = (new tag_query($tag_name,$all_ids))->get();
        $id_keys = array_flip(array_column($tags,'ID'));
        foreach ($ids as $key => $id) {
            $tag = $tags[$id_keys[$id]];
            $k = &$settings[$tag_name][$key];
            $k = $tag;
        }
    }
    
    return ['code'=>1,'settings'=>$settings];
}
function api_feed() {
    required_login();
    required_params("type");
    $d = &$_POST['data'];
    
    $page = intval($_POST['data']['page'] ?? 1);
    global $book_query;
    global $app;
    $type = in_array($d['type'],['feed','reading']) ? $d['type'] : 'reading';
    $book_query = feed::get($type,$page);

    $response = !$book_query ? ['book_collections'=>[],'pages'=>1,'ids'=>[]] : [
        'book_collections'  => collection_helpers::query_by_book(array_column($book_query->books,'ID')),
        'pages'             => $book_query->pages,
        'ids'               => array_column($book_query->books,'ID')
    ];
    ob_start();
    if (!$book_query) {
        $app->template('/subviews/no-feed');
    }
    else if ( $book_query->has() ){
        global $book;
        foreach ($book_query->books as $book) {
            $app->template( '/subviews/story-single' );
        }
    }
    else {
        $app->template("/subviews/$type-empty");
    }
	$response['output'] = ob_get_contents();
    ob_end_clean();
    return $response;
}