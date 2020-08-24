<?php
function api_verify_user () {
    required_login();
    required_params('id');
    $d = &$_POST['data'];
    $ffn_id = $d['id'];
    if ($ffn_id === ''){
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