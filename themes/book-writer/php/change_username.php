<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax'])  && isset($_POST['new_username']) && isset($_POST['password'])) {
	$user_id = get_current_user_id();
	if (is_wp_error(wp_authenticate(get_userdata(get_current_user_id())->user_login,$_POST['password']))){
		echo '6';

		exit();
	}
	else if (get_userdata(get_current_user_id())->user_login == $_POST['new_username']){
		echo '5';
	}
	else if (! username_possible($_POST['new_username'])){
		echo '6';
		exit();
	}
	else{

		$wpdb->update($wpdb->users, array('user_login' => $_POST['new_username']), array('ID' => $user_id));
		$wpdb->update($wpdb->users, array('user_nicename' => $_POST['new_username']), array('ID' => $user_id));
		$creds = array(
		    'user_login' => $_POST['new_username'],
		    'user_password' => $_POST['password'],
		    'remember' => true
		);
		echo '1';
	}
	exit();
}