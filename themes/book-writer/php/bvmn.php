<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

$meta_query = array(
	'relation' => 'OR',
	array(
		'key'     => 'source_author_link',
		'compare' => '=',
		'value'   => ''
    ),
    array(
		'key'     => 'source_author_link',
		'compare' => 'NOT EXISTS',
    ),
);
$args = array(
    'post_type'              => array( 'book' ),
    'posts_per_page'		 => 100,
    'meta_query'             => $meta_query,
    'fields'                 => 'ids'
);
$book_query = new WP_Query( $args );
$num_pages = $book_query->max_num_pages;
$books = $book_query->posts;

for ($i=1; $i <= $num_pages; $i++) {
    if ($i > 1){
        $args['paged'] = $i;
        $books = (new WP_Query( $args ))->posts;
    }
    foreach ($books as $id) {
        $full = get_post_meta( $id,'source_author',true );
        $full_arr = explode('">',$full);
        $author_link = ltrim(ltrim($full_arr[0],'<a href='),'"');
        $author_name = rtrim($full_arr[1],'</a>');
        update_post_meta( $id,'source_author_name',$author_name);
        update_post_meta( $id,'source_author_link',$author_link);
    }
}
