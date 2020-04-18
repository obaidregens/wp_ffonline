<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['user'])){
    if(! is_user_logged_in()){
        echo 3;
    }
    else if (! is_in_block_list($_POST['user'])){
        add_to_block_list($_POST['user']);
        echo 0;
    }
    else{
        delete_from_block_list($_POST['user']);
        echo 1;
    }
    exit;
}