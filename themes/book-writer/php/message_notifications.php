<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    if (is_user_logged_in()){
        if ($_POST['data'] == 'count'){
            echo count(get_stats_of('notifications',get_current_user_id(),'last month'));
        }
        else if ($_POST['data'] == 'full'){
            get_template_part('template-parts/header','notifications');
        }
    }
    else{
        echo 0;
    }
 exit;
}