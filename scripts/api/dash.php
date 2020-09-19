<?php
function api_new_tax() {
    required_admin();
    required_params('tax','name');
    $d = &$_POST['data'];
    wp_insert_term( $d['name'], $d['tax'] );
    return ['code'  => 1];
}
function api_reply_to_contact() {
    required_admin();
    required_params('to','message');
    $d = &$_POST['data'];

    $message = $d['message'] ?? "";
    if (trim($d['message'] === "") || !filter_var($d['to'], FILTER_VALIDATE_EMAIL) ) {
        return ['code'=>8];
    }
    global $wpdb;
    $wpdb->insert(
        'contact',
        [
            'user_id'       => get_current_user_id(),
            'from'          => "contact@fanfiction.online",
            'to'            => $d['to'],
            'received_time' => microtime(true),
            'subject'       => '',
            'headers'       => '',
            'message'       => $d['message']
        ]
    );
}