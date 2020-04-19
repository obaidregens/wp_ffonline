<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    $_POST['id'] = intval($_POST['id']);
    if (isset($_POST['to_delete'])){
        delete_search($_POST['id']);
        if (saved_search_exists('',$_POST['id']) == false){
            echo 2;
            exit();
        }
    }
    if (! is_user_logged_in()){
        echo 3;
        exit();
    }
    $exists = saved_search_exists($_POST['name'],$_POST['id']);
    if ($_POST['name'] == ''){
        echo 'Name cannot be empty.';
        exit();
    }
    else if ($exists){
        echo $exists;
        exit();
    }
    $save_search_r = save_search($_POST['name'],$_POST['id']);
    if ($save_search_r != false){
        echo 1;
        exit();
    }
    else{
        //output false
        echo 'An error occured.';
        exit();
    }
 exit;
}