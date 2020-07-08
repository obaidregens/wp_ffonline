<?php
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
 	global $template;
	$to_remove = str_replace('functions.php','',__FILE__);
	$to_remove = str_replace('\\','/',$to_remove);
	$template = str_replace('\\','/',$template);
	$template_pages = str_replace($to_remove,"",$template);
	//jQuery
	if (! is_admin()){
    	wp_deregister_script( 'jquery' );
    	wp_register_script('jquery', false);
	}

}