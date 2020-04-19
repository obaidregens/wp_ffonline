<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 	global $template;
	$to_remove = str_replace('functions.php','',__FILE__);
	$to_remove = str_replace('\\','/',$to_remove);
	$template = str_replace('\\','/',$template);
	$template_pages = str_replace($to_remove,"",$template);
	//jQuery
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'starter-style', get_stylesheet_uri() );
    //wp_enqueue_script( 'includes', get_template_directory_uri() . '/js/min/includes.min.js', '', '', true );

	//General Includes
	wp_enqueue_style( 'materialize_css', get_stylesheet_directory_uri() . '/materialize/css/materialize-input.min.css');	

    wp_enqueue_style( 'nouislider_css', get_stylesheet_directory_uri() . '/materialize/extras/nouislider.css');
	wp_enqueue_script('nouislider_js', get_stylesheet_directory_uri() .'/materialize/extras/nouislider.js');
	wp_enqueue_script('materialize_js', get_stylesheet_directory_uri() .'/materialize/js/materialize.min.js', array('jquery'), null, true);



	wp_enqueue_script('global', get_stylesheet_directory_uri() .'/js/global.js', array('jquery'), null, true);
	//wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style',get_stylesheet_directory_uri() . '/style.css');
	//Specific Includes
    if($template_pages == 'page-dashboard.php'){
	    wp_enqueue_script('dashboard_js', get_stylesheet_directory_uri() .'/js/dashboard.js', array('jquery'), null, true);
	    wp_enqueue_style( 'dashboard_css', get_stylesheet_directory_uri() .'/css/dashboard.css');
	}
	if ($template_pages == 'page-search.php' || $template_pages == 'taxonomy-collection.php'){
		wp_enqueue_script('page_read_js', get_stylesheet_directory_uri() .'/js/read.js', array('jquery'), null, true);
	}
}