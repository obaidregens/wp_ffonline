<?php
function api_contact() {
    $d = &$_POST['data'];
    $message = $d['message'];
    $email = $d['email'] ?? 'None specified';
    $author = is_user_logged_in(  ) ? get_user_by( 'ID', get_current_user_id() )->display_name : 'None';
    $email_text = 
"
Label- Misc

Message From: $email

Author: $author

Message:

$message

";
    wp_mail( 'info@fanfiction.online', 'Message from Fanfiction Online', $email_text );
    return ['code'=>1];
}