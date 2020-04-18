<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['username']) ){
    if (username_exists($_POST['username'])) ){
        echo '1';
    }
    else if ($_POST['username'] == get_the_author_meta('user_login',get_current_user_id()){
    	echo '2';
    }
    else{
        echo '0';
    }
 exit;
}