<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    if (! is_user_logged_in()){
        echo 3;
        exit();
    }
    if (get_post($_POST['id']) == null){
        echo 6;
        exit();
    }
    if (in_array($_POST['id'],get_stats_of('user_hidden',get_current_user_id()))){
        delete_hidden($_POST['id']);
        echo 0;
        exit();
    }
    add_to_hidden($_POST['id']);
    echo 1;
 exit;
}