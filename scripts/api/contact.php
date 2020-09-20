<?php
function api_contact() {
    required_params('message','email');
    $d = &$_POST['data'];
    $message = stripslashes($d['message'] ?? "");
    if (trim($message === "")) {
        return ['code'=>8];
    }
    $email = stripslashes($d['email'] ?? "");
    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        return ['code'=>9];
    }
    global $wpdb;
    $wpdb->insert(
        'contact',
        [
            'user_id'       => get_current_user_id(),
            'from'          => $email,
            'to'            => 'Contact',
            'received_time' => microtime(true),
            'subject'       => '',
            'headers'       => '',
            'message'       => $message,
            'message_id'    => "",
            'vfs'           => $_COOKIE['vfs'],
        ]
    );
    return ['code'=>1];
}