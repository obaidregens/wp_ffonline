<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    function return_code($code,$extra = 0){
		$_return = array(
			'code'				=>	$code,
			'book_collections'	=>	collection_data(array($_POST['book_id']))['book_collections']
		);
		if ($extra !== 0){
			$_return['extra'] = $extra;
		}
		echo json_encode($_return);
		exit();
	}
    if (! is_user_logged_in()){
        return_code(3);
        exit();
    }
    if (get_post($_POST['id']) == null){
        return_code(6);
        exit();
    }
    if (in_array($_POST['id'],get_stats_of('user_hidden',get_current_user_id()))){
        delete_hidden($_POST['id']);
        return_code(0);
        exit();
    }
    add_to_hidden($_POST['id']);
    return_code(1);
    exit;
}