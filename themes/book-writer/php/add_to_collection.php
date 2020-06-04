<?php

define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

$_POST = json_decode(str_replace('\\','',$_POST['data_']),true);

if( isset($_POST['ajax'])){
	function return_code($code,$extra = 0){
		$_return = array(
			'code'				=>	$code,
		);
		if ($code <= 5){
			$_return['book_collections'] = collection::query_by_book($_POST['book_ids'],'ID');
		}
		if ($extra !== 0){
			$_return['extra'] = $extra;
		}
		echo json_encode($_return);
		exit();
	}
	if (! is_user_logged_in()){
		return_code(6);
	}
	$_POST['book_ids'] = array_keys($_POST['book_collections']);
	foreach ($_POST['book_ids'] as $key => $book_id) {
		$return = collection::set('book',$book_id,$_POST['book_collections'][$book_id],null,get_current_user_id());
		if (err::is($return) ){
			return_code($return);
		}
	}
	return_code(1);
}