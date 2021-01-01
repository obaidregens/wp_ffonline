<?php
function api_search() {
    global $book_query;
    global $app;
    $book_query = new book_query;
    if (
        isset($_POST['data']['page'])
        && is_numeric($_POST['data']['page'])
    ){
        $book_query->args = ctrk_decrypt($_POST['data']['prev'],true);
        $book_query->args['page'] = $_POST['data']['page'];
    }
    else{
        $book_query->args_from_url($_POST['data']['search']);
        $book_query->args = type_args($book_query->args,ctrk_decrypt($_POST['placeholder'],true));
    }
    $book_query->args['is_search'] = true;
    $book_query->query();
    $response = array(
        'prev'              => ctrk_encrypt($book_query->args),
        'book_collections'  => collection_helpers::query_by_book(array_column($book_query->books,'ID')),
        'pages'             => $book_query->pages
    );
    ob_start();
	if ( $book_query->has() ){
        global $book;
        foreach ($book_query->books as $book) {
            $app->template( '/subviews/story-single' );
        }
	}
	else {
        $app->template('/subviews/no-books');
    }
	$response['output'] = ob_get_contents();
    ob_end_clean();
    $response['ids'] = array_column($book_query->books,'ID');
    return $response;
}
function api_load_tags() {
    required_params('prev','tag','search');
    $d = &$_POST['data'];
    $s = (string) $d['search'];

    $args = ctrk_decrypt($d['prev'],true);
    $args['per_page'] = 1;
    $book_query = new book_query($args);    

    $d['selected']['included'] = (array) ($d['selected']['included'] ?? []);
    $d['selected']['excluded'] = (array) ($d['selected']['excluded'] ?? []);

    $terms =
    (new tag_query($d['tag'], $book_query->ids))
    ->search($s)
    ->select($d['selected'])
    ->sort()
    ->get();

    if (!($d['all'] ?? false)) {
        $more = count($terms) > 30;
        array_splice($terms,30);    
    }

    $list = [];
    foreach ($terms as $k => $term ) {
        $list[] = [
            'value'     => $term['ID'],
            'name'      => $term['name'],
            'count'     => $term['count'],
            'selected'  => $term['selected'] ?? null
        ];
    }
    return [
        'code'      => 1,
        'result'    => [
            'list'      => $list,
            'more'      => $more ?? false
        ]
    ];
}
function api_get_tags() {
    required_params('tag','ids');
    $d = &$_POST['data'];
    
    if (!in_array($d['tag'],book_query::$taxonomies)){
        return ['code'=>8,'names'=>[]];
    }

    global $wpdb;
    $sql = $wpdb->prepare(
        "SELECT ids FROM `search_cache` WHERE (`_key`,`_value`) = (%s,%s)",
        ['tag_names',$d['tag']]
    );
    $r = $wpdb->get_results($sql);
    $ids = empty($r) ? [] : unserialize($r[0]->ids);
    $r = [];
    $d['ids'] = (array) $d['ids'];
    foreach ($d['ids'] as $id ) {
        $r[$id] = $ids[$id] ?? false;
    }

    return [
        'code'      => 1,
        'names'     => $r
    ];
}