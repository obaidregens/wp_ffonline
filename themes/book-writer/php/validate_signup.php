<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['login']) && isset($_POST['email'])){
    if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        echo '8';
        exit();
    }
    if (! username_possible($_POST['login']) && email_exists($_POST['email'])){
        echo '12';
    }
    else if (! username_possible($_POST['login'])){
        echo '1';
    }
    else if (email_exists($_POST['email'])){
        echo '2';
    }
    else{
        register_new_user($_POST['login'],$_POST['email']);
        echo '0';
    }
 exit;
}