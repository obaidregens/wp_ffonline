<?php
function api_login(){
    required_params('password','username');
    $username = $_POST['data']['username'];
    if (substr($username,0,1) === "@"){
        $username = substr($username,1);
    }
    $pass = $_POST['data']['password'];
    $return = user::login($username,$pass);
    if ($return === false){
        return ['code'=>7];
    }
    return ['code'=>1];
}
function api_login_with_code(){
    required_params('username');
    $username = $_POST['data']['username'];
    if (substr($username,0,1) === "@"){
        $username = substr($username,1);
    }
    $return = user::send_code($username);
    if ($return === false){
        return ['code'=>7];
    }
    return [
        'code' => 1,
        'token'     => ctrk_encrypt(array(
            'token'     => $return
        ))
    ];
}
function api_signup(){
    required_params('email','username');
    $return = user::signup($_POST['data']['email'],$_POST['data']['username']);
    if (err::is($return)){
        return [
            'code'      => 7,
            'errors'    => array_column($return->errors,'error','name')
        ];
    }
    return [
        'code'      => 1,
        'token'     => ctrk_encrypt(array(
            'token'     => $return
        ))
    ];
}
function api_verify_code(){
    $datal = &$_POST['data'];
    required_params('action','token','code');
    $datal['token'] = ctrk_decrypt($datal['token'])->token;
    if ($_POST['data']['action'] === 'signup'){
        required_params('email','username');
        $return = user::verify( $datal['code'],$datal['token'],$datal['email'], $datal['username'] );
    }
    else if ($_POST['data']['action'] === 'login_with_code'){
        required_params('username');
        $return = user::login_code($datal['username'],$datal['token'],$datal['code']);
    }
    else {
        return ['code'=>9];
    }
    if ($return === false){
        return ['code'=>7];
    }
    return ['code'=>1];
}