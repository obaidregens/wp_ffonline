<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');


if( isset($_POST['ajax'])) {
	if(! headers_sent() && ! isset($_SESSION) ){ 
		session_start(); 
	}
	if (! in_array(intval($_POST['search_id']),$_SESSION['search_ids']) ){
		print_r( array('output' => 6));
		exit();
	}
 	global $force_template_page;
 	global $force_template_id;
	$prev_search = get_search(intval($_POST['search_id']));
	$force_template_page = $prev_search['type'];
	$force_template_id = $prev_search['type_id'];
	if (isset($_POST['search_id']) && isset($_POST['page']) && in_array(intval($_POST['search_id']),$_SESSION['search_ids']) ){
		$args = $prev_search['args'];
		$args['paged'] = intval($_POST['page']);
	}
	else{
		$args = unpack_search($_POST);
	}
	// The Query
	$default_query = new WP_Query( $args );
	$original_query = $wp_query;
	$wp_query = null;
	$wp_query = $default_query;
	$response = array();
	$response['search_id'] = log_search($args);
	$response['pages'] = $wp_query->max_num_pages;
	ob_start();
	if (have_posts()){
		// Start the loop.
		while (have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'search' );

		// End the loop.
		endwhile;
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
	//Reset Data
	//This is because we dont want our meddling of the $wp-query to affect the whole site
	$wp_query = null;
	$wp_query = $original_query;
	wp_reset_postdata();
	print_r(json_encode($response));
    exit();
}