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
    $template = "contact";
    $email_params = [
        "txt_message"       => $d["message"],
        "html_message"      => str_replace(["\r\n", "\r", "\n"], "<br/>", $d['message']),
    ];
    if (empty($r)) {
        $all_from = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM contact WHERE `from` = '%s' AND `to` = 'Contact' ORDER BY received_time DESC"
        , [$d['to']] ))[0] ?? false;
        if ($all_from !== false) {
            $email_params['template'] = 'contact-first';
            $email_params['txt_prev_message'] = $all_from->message;
            $email_params['html_prev_message'] = str_replace(["\r\n","\r","\n"], "<br/>", $all_from->message);    
        }
    }
    // if (! empty($r)) {
    //     $email_params['reply-to'] = $r[0]->message_id;
    // }
    
    $id = (new SendGrid($template,$email_params,SENDGRID_contact))->send([$d['to']]);

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
function api_archive_from_contact() {
    required_admin();
    required_params('from');
    $d = &$_POST['data'];

    global $wpdb;
    $wpdb->insert(
        'contact',
        [
            'user_id'       => get_current_user_id(),
            'from'          => "Archived",
            'to'            => $d['from'],
            'received_time' => microtime(true),
            'subject'       => "Archived",
            'headers'       => '',
            'message'       => "Archived",
            'message_id'    => "",
            'vfs'           => ""
        ]
    );
    return ['code'=>1];
}
function api_reply_to_question() {
    required_admin();
    required_params('question_id','question','answer','category');
    $d = &$_POST['data'];
    questions::answer(
        $d['question_id'],
        $d['answer'],
        $d['question'],
        $d['category'],
        intval(($d['link'] ?? 0) ?: 0)
    );
    return ['code'=>1];
}
function api_archive_question() {
    required_admin();
    required_params('id');
    $d = &$_POST['data'];
    questions::delete($d['id']);
    return ['code'=>1];
}
function api_contact_question() {
    required_admin();
    required_params('id');
    $q = questions::get($_POST['data']['id']);

    global $wpdb;
    $vfs = $wpdb->get_results($wpdb->prepare("SELECT vfs FROM stats_landings WHERE ID = %d",[$q->landing_id]));
    if (empty($vfs)) {
        return ['code'=>11];
    }
    $wpdb->insert(
        'contact',
        [
            'user_id'       => $q->user_id,
            'from'          => $q->email,
            'to'            => "FAQ",
            'received_time' => intval($q->asked_millitime)/1000,
            'subject'       => "",
            'headers'       => "",
            'message'       => $q->question,
            'message_id'    => "",
            'vfs'           => $vfs[0]->vfs
        ]
    );
    questions::delete($q->ID);
    return ['code'=>1];
}
function api_allow_reimport() {
    required_admin();
    required_params('user_id','allow');
    $d = &$_POST['data'];
    if (substr($d['user_id'],0,1) === "@") {
        $d['user_id'] = substr($d['user_id'],0);
    }
    $user = get_user_by( "login", $d['user_id'] );
    if (! $user) {
        $user = get_user_by( "ID", $d['user_id'] );
    }
    if (!$user) {
        return ['code'=>10];
    }
    $users = get_option( 'reimport_allow', [] );
    if ($d['allow']) {
        $users[] = intval($user->ID);
    }
    else {
        arr::remove($users,intval($user->ID));
    }
    update_option( "reimport_allow", array_unique($users) );
    return ['code'=>1];
}
function api_beta_session_create() {
    required_admin();
    required_params('description','duration','users','start_url','custom');
    $d = &$_POST['data'];
    $beta_id = beta::new_session([
        'description'   => $d['description'],
        'start_url'     => "https://beta.fanfiction.online/" . ltrim($d['start_url'],'/'),
        'users'         => intval($d['users']),
        'custom'        => arr::non_empty(explode(',',$d['custom'])),
        'duration'      => floatval($d['duration'])*1000*1*60*60
    ]);
    if (err::is($beta_id)) {
        return ['code'=>9,'beta_id'=>$beta_id->array()];
    }
    return ['code'=>1,'beta_id'=>$beta_id];
}
function api_verify_confirm() {
    required_admin();
    required_params("id");
    $d = $_POST['data'];

    global $wpdb;

    $sql = 
    "SELECT `user_id`,`connection_user` as `ffn_user`
    FROM user_connections
    WHERE ID = %s AND status = 'unverified'";
    $sql = $wpdb->prepare($sql,[$d['id']]);
    $a = $wpdb->get_results($sql);
    if (empty($a)) {
        return ['code'=>9];
    }
    $a = $a[0];

    $wpdb->update(
        "user_connections",[
            'status'            => 'verified',
            'link_timestamp'    => time(),
        ],[
            'ID'                => $d['id']
        ]
    );

    return ['code'=>1];
}