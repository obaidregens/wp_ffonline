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

    if (trim($d['message'] === "") || !filter_var($d['to'], FILTER_VALIDATE_EMAIL) ) {
        return ['code'=>8];
    }
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM contact WHERE `from` = %s AND `to` = 'contact@fanfiction.online' ORDER BY received_time DESC", [$d['to']] );
    $r = $wpdb->get_results($sql);
    $subject = 'Reply from Fanfiction Online';
    if (! empty($r)) {
        $subject = $r[0]->subject;
        if (substr($subject,0,4) === "Re: ") {
            $subject = substr($subject,4);
        }
        $subject = "Re: " . $subject;
    }
    $email_params = [
        'to'        => $d['to'],
        'from'      => 'contact',
        'template'  => 'contact',
        'subject'   => $subject,
        'params'    => [
            '###SUBJECT###' => 'Reply from Fanfiction Online',
        ],
        'txtparams'    => [
            '###MESSAGE###' => $d['message'],
        ],
        'htmlparams'    => [
            '###MESSAGE###' => str_replace(["\r\n", "\r", "\n"], "<br/>", $d['message']),
        ],
    ];
    if (empty($r)) {
        $all_from = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM contact WHERE `from` = '%s' AND `to` = 'Contact' ORDER BY received_time DESC"
        , [$d['to']] ))[0] ?? false;
        if ($all_from !== false) {
            $email_params['template'] = 'contact-first';
            $email_params['txtparams']['###PREV_MESSAGE###'] = $all_from->message;
            $email_params['htmlparams']['###PREV_MESSAGE###'] = str_replace(["\r\n","\r","\n"], "<br/>", $all_from->message);    
        }
    }
    if (! empty($r)) {
        $email_params['reply-to'] = $r[0]->message_id;
    }
    $id = email($email_params);
    $wpdb->insert(
        'contact',
        [
            'user_id'       => get_current_user_id(),
            'from'          => "contact@fanfiction.online",
            'to'            => $d['to'],
            'received_time' => microtime(true),
            'subject'       => $subject,
            'headers'       => '',
            'message'       => $d['message'],
            'message_id'    => $id,
            'vfs'           => ""
        ]
    );
    return ['code'  => 1];
}