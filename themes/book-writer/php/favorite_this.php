<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['book_id'])){
    if (! is_user_logged_in()){
        echo 3;
        exit();
    }
    if (get_post($_POST['book_id']) == null){
        echo 6;
        exit();
    }
    else if (in_array(get_current_user_id(),get_stats_of('book_fav',$_POST['book_id']))){
        delete_favorite_book($_POST['book_id']);
        echo 0;
    }
    else{
        add_favorite_book($_POST['book_id']);
        echo 1;
    }
 exit;
}