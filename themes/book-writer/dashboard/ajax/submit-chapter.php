<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

function return_code($code,$extra = 0){
	$_return = array(
		'code'			=>	$code,
		'chapter_id'	=>	$_POST['chapter_id']
	);
	if ($extra !== 0){
		$_return['extra'] = $extra;
	}
	echo json_encode($_return);
	exit();
}

if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}
if(! isset($_SESSION['nonce_key']) || !isset($_POST['ajax']) || $_POST['ajax'] != $_SESSION['nonce_key'] ){
	return_code(8);
}
$_POST = process_data($_POST,'chapter');
if (! $_POST){
	return_code(9);
}
$book = get_post($_POST['book_id']);
if ($_POST['publish'] == 'true'){
	if (! can_publish($_POST,'chapter')){
		return_code(10);
	}
	if ($book->post_status != 'publish'){
		return_code(11);
	}
}

// Add the content of the form to $post as an array
$new_post = array(
	'post_title'		=> htmlspecialchars($_POST['title']),
	'post_content'  	=> $_POST['content'],
	'post_type'			=> 'chapter',
	'post_status'   	=> 'draft',
	'comment_status'	=> 'closed',
	'post_parent'		=> $_POST['book_id'],
);
if ($_POST['publish'] == 'true'){
	$new_post['post_status'] = 'publish';
}
if ($_POST['comments'] == 'true'){
	$new_post['comment_status'] = 'open';
}

if ($_POST['chapter_id'] == 'new'){
	$chapter_id = wp_insert_post($new_post);
}
else{
	$chapter_id = $_POST['chapter_id'];
	$new_post['ID'] = $chapter_id;
	wp_update_post($new_post);
}
$updated = get_post($chapter_id);
if ($updated->post_status == 'publish'){
	return_code(1);
}
else if($updated->post_status == 'draft'){
	if (empty(published_chapters($_POST['book_id']))){
		wp_update_post(array(
			'ID'			=> $_POST['book_id'],
			'post_status'	=> 'draft'
		));
	}
	return_code(2);
}
return_code(7);
exit();