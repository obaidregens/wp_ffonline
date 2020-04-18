<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( 'POST' == $_SERVER['REQUEST_METHOD']) {
	foreach($_POST as $value){
		$value = strip_tags($value,array('<p>','<br>','<strong>','<em>','<u>','<i>'));
	}
    // Required Fields
    $bookid = $_POST['book-id'];
    $title =  $_POST['chapter_title'];
	$content = $_POST['chapter_content'];
	//Optional Fields
	$pre = $_POST['pre_chapter'];
	$post = $_POST['post_chapter'];
    // Add the content of the form to $post as an array
    $new_post = array(
        'post_title'    	=> $title,
        'post_content'  	=> wpautop($content),
        'post_status'   	=> 'draft',  
        'post_type'			=> 'chapter',
        'post_parent'       => $bookid,
    );
    //save the new post and return its ID
    $post_id = wp_insert_post($new_post);
    update_post_meta($post_id,'pre-chapter_notes',$pre);
    update_post_meta($post_id,'post-chapter_notes',$post);

}
wp_redirect('https://www.fanfiction.online/dashboard/write');
exit();