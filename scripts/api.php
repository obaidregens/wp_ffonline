<?php
ignore_user_abort(true);

$json_raw = json_decode(file_get_contents('php://input'),true);
if (!is_array($json_raw)) {
    $json_raw = [];
}
$_POST = $json_raw;

$import = [
    'global',
    'author',
    'chapter',
    'collections',
    'inbox',
    'login',
    'reviews',
    'search-main',
    'verify',
    'contact',
    'drafts',
    'edit-book',
    'news',
    'import',
    'dash',
    'faq',
    'poll',
    'offline',
    'beta',
    'help',
];
foreach ($import as $filename) {
    require_once(__DIR__ . '/api/' . $filename . '.php');
}
if( !headers_sent() && !isset($_SESSION) ){ 
    session_start(); 
}
function required_params(...$params){
    foreach($params as $param){
        if (! isset($_POST['data'][$param])){
            spam::add("required_params",$_POST['landing_id'],"Action: " . $_POST['action'] . " - Param: " . $param);
            echo json_encode(array(
                'code'      => 996
            ));
            exit();
        }
    }
}
function required_login(){
    if (! is_user_logged_in()){
        spam::add("required_login",$_POST['landing_id'],$_POST['action']);
        echo json_encode(array(
            'code'  => 995
        ));
        exit();
    }
}
function required_admin(){
    if (! current_user_can('administrator')){
        spam::add("required_admin",$_POST['landing_id'],$_POST['action']);
        echo json_encode(array(
            'code'  => 994
        ));
        exit();
    }
}
if (empty($_COOKIE)) {
    spam::add("no_cookies","",json_encode($_COOKIE));
}
if (!isset($_COOKIE["vfs"])) {
    spam::add("no_vfs","",json_encode($_COOKIE));
}
$placeholder = ctrk_decrypt($_POST['placeholder'] ?? "");
if ( trim($_POST['placeholder'] ?? '') === "" || !$placeholder ) {
    spam::add("invalid_placeholder","",$placeholder);
    echo json_encode(array(
        'code'      => 993
    ));
    exit();
}
else {
    $_POST['landing_id'] = $placeholder->landing_id;
}
$reCAPTCHA_apis = [
    'login',
    'login_with_code',
    'signup',
    'verify_code',
    'contact',
    'publish_review',
    'ask_question',
    'change_email',
    'change_email_confirm'
];
if ( in_array($_POST['action'] ?? [],$reCAPTCHA_apis) ){
    if (! isset($_POST['reCAPTCHA']) || verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        echo json_encode(array(
            'code'      => 997
        ));
        exit();
    }
}
else if (
    isset($_POST['nonce'])
    && isset($_SESSION['nonce'])
    && is_array($_SESSION['nonce'])
    && in_array($_POST['nonce'],$_SESSION['nonce'])
    ){}
else{
    spam::add("invalid_nonce",$_POST['landing_id']);
    echo json_encode(array(
        'code'  => 998
    ));
    exit();
}
//Call User Functions
if (! function_exists('api_' . $_POST['action'])){
    spam::add("invalid_action",$_POST['landing_id'],$_POST['action']);
    echo json_encode(array(
        'code'      => 999
    ));
    exit();
}
$_SESSION['landing_cache'] = $_SESSION['landing_cache'] ?? [];
$lref = &$_SESSION['landing_cache'][$_POST['landing_id']];
$lref = $lref ?? null;
$landing = landing::use($lref ?: $_POST['landing_id']);
if (err::is($landing)) {
    echo json_encode([
        'code'      => 992
    ]);
    exit();
}
$lref = $landing;
$b = call_user_func('api_' . $_POST['action']);
if ($b) {
    echo json_encode($b);
}
exit();