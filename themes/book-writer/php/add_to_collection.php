<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
	if (! is_user_logged_in()){
		echo 3;
		exit();
	}
	if (get_post($_POST['book_id']) == null){
		echo 6;
		exit();
	}
	foreach ($_POST['collections'] as $term_id) {
		if (get_term_meta($term_id,'author',true) != get_current_user_id()){
			echo 6;
			exit();
		}
	}
    wp_set_post_terms($_POST['book_id'],$_POST['collections'],'collection');
 exit;
}