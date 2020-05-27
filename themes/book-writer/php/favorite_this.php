<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['book_id'])){
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
    if (get_post($_POST['book_id']) == null){
        return_code(6);
        exit();
    }
    else if (in_array(get_current_user_id(),get_stats_of('book_fav',$_POST['book_id']))){
        delete_favorite_book($_POST['book_id']);
        return_code(0);
    }
    else{
        add_favorite_book($_POST['book_id']);
        return_code(1);
    }
 exit;
}