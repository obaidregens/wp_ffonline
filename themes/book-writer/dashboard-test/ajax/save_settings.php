<?php
define('WP_USE_THEMES', false);
require('/home3/eshaatco/public_html/fic/wp-load.php');

if( isset($_POST['ajax'])) {
    update_user_meta(get_current_user_id(),'fav_notify',$_POST['emails_notifications']);
    update_user_meta(get_current_user_id(),'read_receipts',$_POST['messages_read']);
    update_user_meta(get_current_user_id(),'online_status',$_POST['messages_online']);
    update_user_meta(get_current_user_id(),'allow_messaging',$_POST['messages_status']);
	exit();
}