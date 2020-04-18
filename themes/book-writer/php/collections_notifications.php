<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['collection'])){
    if(! is_user_logged_in()){
        echo 3;
    }
    else if ($_POST['collection'] == 'favorites'){
        if (get_user_meta(get_current_user_id(),'fav_notify',true) == 'notify' || get_user_meta(get_current_user_id(),'fav_notify',true) === ''){
            update_user_meta(get_current_user_id(),'fav_notify','disable');
            echo 0;
        }
        else{
            update_user_meta(get_current_user_id(),'fav_notify','notify');
            echo 1;
        }
    }
    else if ($_POST['collection'] == 'hidden'){
        if (get_user_meta(get_current_user_id(),'hidden_notify',true) == 'notify' || get_user_meta(get_current_user_id(),'hidden_notify',true) === ''){
            update_user_meta(get_current_user_id(),'hidden_notify','disable');
        }
        else{
            update_user_meta(get_current_user_id(),'hidden_notify','notify');
        }
    }
    else if (in_array(get_current_user_id(),get_stats_of('collection_follow',$_POST['collection']))){
        //Exists
        delete_follow_collection($_POST['collection']);
        echo 0;
    }
    else{
        add_follow_collection($_POST['collection']);
        echo 1;
    }
 exit;
}