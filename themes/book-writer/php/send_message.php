<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['email']) && isset($_POST['message']) && isset($_POST['label'])){
	foreach($_POST as $value){
		$value = strip_tags($value,array('<p>','<br>','<strong>','<em>','<u>','<i>'));
	}
	$message = 'Label- ' . $_POST['label'] . "\r\n\r\n";
    $message .= 'Message From: ' . $_POST['email'] . "\r\n\r\n";
	if (get_current_user_id() != 0){
		$message .= 'Author: ' . home_url('author/') . get_the_author_meta('user_nicename',get_current_user_id()) . "\r\n\r\n";
	}
	$message .= 'Message: ' . "\r\n\r\n";
	$message .= $_POST['message'] . "\r\n\r\n";
	echo wp_mail('info@fanfiction.online','Message from Fanfiction Online',$message);
 exit;
}