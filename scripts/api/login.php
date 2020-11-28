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
    $start = microtime(true);
    $return = user::send_code($username);
    $sleep = 7 - (microtime(true) - $start);
    $tok = anon_token($return);
    if ($sleep > 0) {
        usleep($sleep*1000000);
    }
    return [
        'code' => 1,
        'token'     => ctrk_encrypt(array(
            'token'     => $tok
        ))
    ];
}
function api_username_check () {
    required_params('username');
    $validation = (new v_user([
        'username'  => $_POST['data']['username'],
    ],['username']))->return;
    if (err::is($validation)){
        return [
            'code'      => 7,
            'error'    => $validation->errors[0]['error']
        ];
    }
    return ['code'=>1];
}
function api_signup(){
    required_params('email','username');
    $return = user::signup(trim($_POST['data']['email']),trim($_POST['data']['username']));
    if (err::is($return)){
        return [
            'code'      => 7,
            'errors'    => array_column($return->errors,'error','name')
        ];
    }
    $tok = anon_token($return);
    return [
        'code'      => 1,
        'token'     => ctrk_encrypt(array(
            'token'     => $tok
        ))
    ];
}
function api_verify_code(){
    required_params('action','token','code');
    $datal = &$_POST['data'];
    $decrypt = ctrk_decrypt($datal['token']);
    if (! $decrypt) {
        return ['code'=>12];
    }
    $datal['token'] = get_anon_token($decrypt->token);
    $datal['code'] = strtolower($datal['code']);
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
function api_google_signin() {
    required_params("token");
    $token = $_POST['data']['token'];
    $r = GoogleAuth::login($token,$_POST['landing_id']);
    if (!$r) {
        return ['code'=>10];
    }
    if ($r['action'] === "login") {
        return ['code'=>1];
    }
    return [
        'code' => 2,
        'token' => ctrk_encrypt([
            'token' => anon_token($r['ID'])]
        )
    ];
}
function api_create_username() {
    required_params('token','username');
    $d = &$_POST['data'];
    $validation = (new v_user([
        'username'  => $d['username'],
    ],['username']))->return;
    if (err::is($validation)){
        return [
            'code'      => 7,
            'errors'    => array_column($return->errors,'error','name')
        ];
    }
    $ID = get_anon_token(ctrk_decrypt($d['token'])->token);
    $r = GoogleAuth::verify($ID,$d['username']);
    if (!$r) {
        return ['code'=>10,'ID'=>$ID];
    }
    return ['code'=>1];
}