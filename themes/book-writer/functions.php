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

	//General Includes
	wp_enqueue_style( 'materialize_css', get_stylesheet_directory_uri() . '/materialize/css/materialize-input.min.css');	

	wp_enqueue_style( 'main',get_stylesheet_directory_uri() . '/style.css');
}