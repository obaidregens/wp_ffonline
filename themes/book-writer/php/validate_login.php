<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['login']) && isset($_POST['password'])){
    if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        echo '8';exit();
    }
	$result = wp_authenticate($_POST['login'],$_POST['password']);
    if (is_wp_error($result)){
        echo '1';
    }
    else{
		$creds = array();
		$creds['user_login'] = $_POST['login'];
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = true;
		$user = wp_signon( $creds, true);
		update_user_meta($user->ID,'user_ip',$_SERVER['REMOTE_ADDR']);
		echo $user->display_name;
    }
 exit;
}