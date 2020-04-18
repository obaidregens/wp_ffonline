<?php
    /**
     * The template for displaying books
     *
    **/
// the query
$default_query = new WP_Query( array(
    'post_type' => 'book',
	'orderby'	=> 'modified',
	'posts_per_page' => 10,
));
$original_query = $wp_query;
$wp_query = null;
$wp_query = $default_query;

get_template_part('search');


//Reset Data
//This is because we dont want our meddling of the $wp-query to affect the whole site

$wp_query = null;
$wp_query = $original_query;
wp_reset_postdata();

