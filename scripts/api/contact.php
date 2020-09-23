<?php
function api_contact() {
    required_params('message','email');
    $d = &$_POST['data'];
    if (trim($d['message'] === "")) {
        return ['code'=>8];
    }
    if ( !filter_var($d['email'], FILTER_VALIDATE_EMAIL) ) {
        return ['code'=>9];
    }
    global $wpdb;
    $wpdb->insert(
        'contact',
        [
            'user_id'       => get_current_user_id(),
            'from'          => $d['email'],
            'to'            => 'Contact',
            'received_time' => microtime(true),
            'subject'       => '',
            'headers'       => '',
            'message'       => $d['message'],
            'message_id'    => "",
            'vfs'           => $_COOKIE['vfs'],
        ]
    );
    return ['code'=>1];
}