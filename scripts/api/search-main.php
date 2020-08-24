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
        $placeholder = ctrk_decrypt($_POST['data']['placeholder'],true);
        $book_query->args = type_args($book_query->args,$placeholder);
    }
    $book_query->query();
    $response = array(
        'prev'      => ctrk_encrypt($book_query->args),
        'tags_data' => tags_data($book_query),
    );
    ob_start();
    ?>
    <book_collections hidden>
        <?= json_encode(collection::query_by_book(array_column($book_query->books,'ID'),'ID')); ?>
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