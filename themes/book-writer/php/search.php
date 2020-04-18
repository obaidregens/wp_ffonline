<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax'])) {
	if(!isset($_SESSION)) 
	{ 
		session_start(); 
	} 
	if (isset($_POST['page']) == false){
		$taxonomies = ['tag','category','rating','language','status','genre','character','pairing'];
		
		$sort = explode('/',$_POST['sort']);
		$_POST['words'][0] = (int)str_replace(array(' Words',','),'',$_POST['words'][0]);
		$_POST['words'][1] = (int)str_replace(array(' Words',','),'',$_POST['words'][1]);
		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => 'word-count',
				'value'   => $_POST['words'][0],
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => 'word-count',
				'value'   => $_POST['words'][1],
				'compare' => '<=',
				'type'    => 'NUMERIC',
			),
		);
		$tax_query = array(
			'relation' => 'AND',
		);
		foreach ($taxonomies as $taxonomy){
			if (isset($_POST[$taxonomy]['included'])){
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'terms'            => $_POST[$taxonomy]['included'],
					'field'            => 'term_id',
					'operator'         => 'AND',		
				);
			}
			if (isset($_POST[$taxonomy]['excluded'])){
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'terms'            => $_POST[$taxonomy]['excluded'],
					'field'            => 'term_id',
					'operator'         => 'NOT IN',	
				);
			}
		}
		/**
		if ($_POST['only'] == 'only'){
			$cats = get_terms( array(
				'taxonomy' => 'category',
				'childless' => true,
			) );
			$excluded_cats = array_diff(array_column($cats, 'slug'),$_POST['category']);
			$tax_query[] = array(
				'taxonomy'			=> 'category',
				'terms'				=> $excluded_cats,
				'field'				=> 'slug',
				'operator'			=> 'NOT IN',		
			);
		}**/
		$args = array(
			'post_type'              => array( 'book' ),
			'post_status'            => array( 'publish' ),
			's'                      => str_replace(" ","+",$_POST['search']),
			'order'                  => $sort[1],
			'orderby'                => $sort[0],
			'meta_query'			 => $meta_query,
			'posts_per_page'		 => 10,
			'post__not_in'	 => get_stats_of('user_hidden',get_current_user_id()),
		);
		if ($sort[0] == 'comment_count'){
			$args['comment_count'] = array(
				'value' => 0,
				'compare' => '>',
			);
		}
		if (empty($tax_query) == false){
			$args['tax_query'] = $tax_query;
			if(strpos($_POST['extras'],'collection') !== false){
				$args['tax_query'][] = array(
					'taxonomy'         => 'collection',
					'terms'            => str_replace('collection_','',$_POST['extras']),
					'field'            => 'term_id',
					'operator'         => 'AND',
				);
			}
		}
		/**if ($_POST['only'] == 'only'){
			$args['cat'] = 50;
		}**/
		$_SESSION["search_args"] = $args;
	}
	else{
		$args = $_SESSION["search_args"];
		$args['paged'] = $_POST['page'];
	}
	// The Query
	$default_query = new WP_Query( $args );
	$original_query = $wp_query;
	$wp_query = null;
	$wp_query = $default_query;
	echo $wp_query->max_num_pages . '<div id="split_max_pages_count"></div>';
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
	//Reset Data
	//This is because we dont want our meddling of the $wp-query to affect the whole site
	$wp_query = null;
	$wp_query = $original_query;
	wp_reset_postdata();
    exit();
}