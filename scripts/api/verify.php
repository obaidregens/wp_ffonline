<?php
function api_verify_user () {
    required_login();
    required_params('source','id');
    $d = &$_POST['data'];
    $ffn_id = $d['id'];
    if (c_user::current() !== false || c_user::pending() !== false) {
        return ['code'=>9];
    } 
    if (trim($ffn_id) === ''){
        return ['code'=>8];
    }
    $return = new c_user(array(
        'connection_user'   => $ffn_id
    ));
    return [
        'code'              => 1,
        'verification_code' => $return->code,
        'account'           => '13818620'
    ];
}
function api_cancel_pending_verification() {
    required_login();
    required_params('source');
    $d = &$_POST['data'];
    if (c_user::current() !== false || c_user::pending() === false) {
        return ['code'=>9];
    }
    c_user::cancel_pending();
    return ['code'=>1];
}