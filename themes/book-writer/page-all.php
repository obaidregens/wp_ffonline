<?php
/**
 * Template Name: All
 *
 * All page
 *
 */
working_page();
get_header();
for ($x = 1; $x <= 1044; $x++) {
    //echo htmlspecialchars('<url><loc>https://fanfiction.online/?page=' . $x . '</loc></url>');
    //echo '<br>';
}
$args = array(
	'post_type'      		 => array( 'book' ),
	'post_status'            => array( 'publish' ),
	'posts_per_page' 		 => -1,
	'order'                  => 'DESC',
	'orderby'                => 'modified',
	'fields'                 => 'ids'
);
$default_query = new WP_Query( $args );
$books = $default_query->posts;
foreach($books as $book){
    //echo htmlspecialchars('<url><loc>' . get_permalink($book) . '</loc></url>');
    //echo '<br>';
}

$args = array(
	'post_type'      		 => array( 'chapter' ),
	'post_status'            => array( 'publish' ),
	'posts_per_page' 		 => 500,
	'order'                  => 'DESC',
	'orderby'                => 'modified',
	'fields'                 => 'ids'
);
$default_query = new WP_Query( $args );
$pages = $default_query->max_num_pages;
$chapters = $default_query->posts;
foreach($chapters as $chapter){
    echo htmlspecialchars('<url><loc>' . get_permalink($chapter) . '</loc></url>');
    echo '<br>';
}
for ($x = 2; $x <= $pages; $x++) {
    $args['paged'] = $x;
    $default_query = new WP_Query( $args );   
    $chapters = $default_query->posts;
    foreach($chapters as $chapter){
        echo htmlspecialchars('<url><loc>' . get_permalink($chapter) . '</loc></url>');
        echo '<br>';
    }    
}
get_sidebar();
get_footer();
	
