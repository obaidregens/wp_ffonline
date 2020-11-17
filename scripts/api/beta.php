<?php
function api_send_beta_feedback() {
    required_params('message');
    $d = &$_POST['data'];
    if (!beta::can() || !beta::is()) {
        return ['code'=>10];
    }
    $user = user::get_by( 'login', 'beta' );
    if (!$user) {
        return ['code'=>11];
    }
    $re = new chats($d['message'],$user->ID);
    if ($re->error->has()) {
        return ['code'=>12,'error'=>$re->error->array()];
    }
    return ['code'=>1];
}
function api_get_beta() {
    if (!beta::can() || !beta::is()) {
        return ['code'=>10];
    }
    $inst = beta::get_current();
    if (!$inst) {
        return ['code'=>11];
    }
    return [
        'code'          => 1,
        'description'   => $inst['description'],
        'id'            => $inst['ID']
    ];
}