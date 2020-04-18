<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['para'])){
    if(! is_user_logged_in()){
        echo 3;
    }
    else if (chapter_bookmark_exists($_POST['chapter'],$_POST['para'])){
        delete_chapter_bookmark($_POST['chapter'],$_POST['para']);
        echo 0;
    }
    else{
        add_chapter_bookmark($_POST['chapter'],$_POST['para']);
        echo 1;
    }
 exit;
}