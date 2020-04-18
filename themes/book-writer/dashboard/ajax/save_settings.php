<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax'])) {
    update_user_meta(get_current_user_id(),'email_me_notifications',$_POST['emails_notifications']);
    update_user_meta(get_current_user_id(),'read_receipts',$_POST['messages_read']);
    update_user_meta(get_current_user_id(),'online_status',$_POST['messages_online']);
    update_user_meta(get_current_user_id(),'allow_messaging',$_POST['messages_status']);
	exit();
}