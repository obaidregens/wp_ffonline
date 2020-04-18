<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 	global $template;
	$to_remove = str_replace('functions.php','',__FILE__);
	$to_remove = str_replace('\\','/',$to_remove);
	$template = str_replace('\\','/',$template);
	$template_pages = str_replace($to_remove,"",$template);
	//Specific Includes
	if ($template_pages == 'page-write.php' || $template_pages == 'page-profile.php' || $template_pages == 'page-login.php' || $template_pages == 'page-reset-password.php' || $template_pages == 'page-contact.php' || $template_pages == 'page-edit-chapter.php' || $template_pages == 'page-search.php' || $template_pages == 'page-create-cat.php' || $template_pages == 'page-dashboard.php' || $template_pages == 'single-chapter.php' || $template_pages == 'single-book.php' || $template_pages == 'taxonomy-collection.php' || $template_pages == 'page-collection.php' || $template_pages == 'author.php'){
	    wp_enqueue_style( 'materialize_css', get_stylesheet_directory_uri() . '/materialize/css/materialize-input.min.css');		
	}
	//General Includes
	else {
	    wp_enqueue_style( 'materialize_css', get_stylesheet_directory_uri() . '/materialize/css/materialize.min.css');
	}
	if ($template_pages == 'page-write.php'){
		wp_enqueue_script('write', get_stylesheet_directory_uri() .'/js/write.js', array('jquery'), null, true);
	}
    wp_enqueue_style( 'nouislider_css', get_stylesheet_directory_uri() . '/materialize/extras/nouislider.css');
	wp_enqueue_script('nouislider_js', get_stylesheet_directory_uri() .'/materialize/extras/nouislider.js');
	wp_enqueue_script('materialize_js', get_stylesheet_directory_uri() .'/materialize/js/materialize.min.js', array('jquery'), null, true);
	wp_enqueue_script('global', get_stylesheet_directory_uri() .'/js/global.js', array('jquery'), null, true);
	//wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style',get_stylesheet_directory_uri() . '/style.css',array( $parent_style ),wp_get_theme()->get('Version'));
	//Specific Includes
    if($template_pages == 'page-dashboard.php'){
	    wp_enqueue_script('dashboard_js', get_stylesheet_directory_uri() .'/js/dashboard.js', array('jquery'), null, true);
	    wp_enqueue_style( 'dashboard_css', get_stylesheet_directory_uri() .'/css/dashboard.css');
	}
	if ($template_pages == 'single-chapter.php' || ($template_pages == 'single-book.php' && isset($_GET['chapter']) && is_numeric($_GET['chapter']) )){
		wp_enqueue_script('accessibility', get_stylesheet_directory_uri() .'/js/accessibility.js', array('jquery'), null, true);
		wp_enqueue_script('js-cookie', get_stylesheet_directory_uri() .'/js/jquery-cookie-master/src/jquery.cookie.js', array('jquery'), 0, true);
	}
	if ($template_pages == 'page-search.php' || $template_pages == 'taxonomy-collection.php'){
		wp_enqueue_script('page_read_js', get_stylesheet_directory_uri() .'/js/read.js', array('jquery'), null, true);
	}
}
