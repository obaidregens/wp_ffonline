<?php
function api_login(){
    required_params('password','username');
    function return_code($code){
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    $username = $_POST['data']['username'];
    $pass = $_POST['data']['password'];
    $return = user::login($username,$pass);
    if ($return === false){
        return_code(7);
    }
    return_code(1);
}
function api_login_with_code(){
    required_params('username');
    function return_code($code,$merge = []){
        echo json_encode(array_merge([
            'code'  => $code
        ],$merge));
        exit();
    }
    $return = user::send_code($_POST['data']['username']);
    if ($return === false){
        return_code(7);
    }
    return_code(1,array(
        'token'     => ctrk_encrypt(array(
            'token'     => $return
        ))
    ));
}
function api_signup(){
    required_params('email','username');
    function return_code($code,$merge = []){
        echo json_encode(array_merge(array(
            'code'  => $code
        ),$merge));
        exit();
    }
    $return = user::signup($_POST['data']['email'],$_POST['data']['username']);
    if (err::is($return)){
        return_code(7,array(
            'errors'     => array_column($return->errors,'error','name')
        ));
    }
    return_code(1,array(
        'token'     => ctrk_encrypt(array(
            'token'     => $return
        ))
    ));
}
function api_verify_code(){
    function return_code($code){
        echo json_encode([
            'code'  => $code
        ]);
        exit();
    }
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
        return_code(9);
    }
    if ($return === false){
        return_code(7);
    }
    return_code(1);
}