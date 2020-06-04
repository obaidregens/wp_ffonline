<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['key']) && isset($_POST['login']) && isset($_POST['password'])){
    if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        echo '8';
        exit();
    }
    if (strlen($_POST['password']) < 8){
        echo '2';
        exit();
    }
    $user = check_password_reset_key($_POST['key'],$_POST['login']);
    if (is_wp_error($user)){
        echo '0';
    }
    else{
        echo '1';
        $return = reset_password($user,$_POST['password']);
        collection::create_default($user->ID);
    }
 exit;
}