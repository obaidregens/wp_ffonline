<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    if (isset($_POST['to_delete'])){
        delete_search($_POST['link']);
        if (saved_search_exists('',$_POST['link']) == false){
            echo 2;
            exit();
        }
    }
    if (! is_user_logged_in()){
        echo 3;
        exit();
    }
    $exists = saved_search_exists($_POST['name'],$_POST['link']);
    if ($_POST['name'] == ''){
        echo 'Name cannot be empty.';
        exit();
    }
    else if ($exists){
        echo $exists;
        exit();
    }
    save_search($_POST['name'],$_POST['link']);
    echo 1;
 exit;
}