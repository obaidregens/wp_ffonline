<?php
function api_search() {
    global $book_query;
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
    $book_query->query();
    $response = array(
        'prev'      => ctrk_encrypt($book_query->args),
        'tags_data' => tags_data($book_query),
    );
    ob_start();
    ?>
    <book_collections hidden>
        <?= json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID'))); ?>
    </book_collections>
    <?php
	if ( $book_query->has() ){
        global $book;
        foreach ($book_query->books as $book) {
            get_template_part( 'template-parts/content' , 'search' );
        }
	}
	else {
		get_template_part( 'template-parts/content', 'noresult' );
    }
	$response['output'] = ob_get_contents();
    ob_end_clean();
	ob_start();
	get_template_part( 'template-parts/content', 'bookpaginate' );
	$response['paginate'] = ob_get_contents();
    ob_end_clean();
    $response['query'] = $book_query;
    return $response;
}
function api_load_character() {
    required_params('s');
    $d = &$_POST['data'];
    $s = (string) "%" . $d['s'] . "%";
    if ($s === "%%") {
        return ['code'=>1,'tags'=>[]];
    }
    $sql =
    "SELECT a.term_id as ID,CONCAT(c.name, ' > ', a.name ) as name FROM wp_terms as a
    INNER JOIN wp_term_taxonomy as b ON a.term_id = b.term_id
    INNER JOIN wp_terms as c ON b.parent = c.term_id
    WHERE b.taxonomy = 'character'
    AND b.count > 0
    AND (
        a.name LIKE %s OR
        c.name LIKE %s
    )";
    // $sql =
    // "SELECT a.term_id as ID,a.name as name, c.name as fandom FROM wp_terms as a
    // INNER JOIN wp_term_taxonomy as b ON a.term_id = b.term_id
    // INNER JOIN wp_terms as c ON b.parent = c.term_id
    // WHERE b.taxonomy = 'character'
    // AND (
    //     a.name LIKE %s OR
    //     c.name LIKE %s
    // )";
    global $wpdb;
    $prepared = $wpdb->prepare($sql,[$s,$s]);
    $r = $wpdb->get_results(
        $prepared
    );
    return ['code'=>1,'tags'=>$r];
}
function api_load_pairing() {
    required_params('s');
    $d = &$_POST['data'];
    $s = (string) $d['s'];
    if ($s === "") {
        return ['code'=>1,'tags'=>[]];
    }
    $sql = "SELECT * FROM search_cache WHERE _key = 'pairing_tag_names'";
    global $wpdb;
    $prepared = $wpdb->prepare($sql,[$s,$s]);
    $r = $wpdb->get_results(
        $prepared
    );
    $e = unserialize($r[0]->ids);
    $tags = [];
    foreach ($e as $id => $name) {
        if (stripos($name, $s) === false) {continue;}
        $tags[] = [
            'ID'    => $id,
            'name'  => $name
        ];
    }
    return ['code'=>1,'tags'=>$tags];
}