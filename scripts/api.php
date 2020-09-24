<?php
function api_poll(){
    $types = _landing::decrypt($_POST['placeholder']);
    $d = &$_POST['data'];
    $d['im_books'] = isset($d['im_books']) ? $d['im_books'] : array();
    $d['im_collections'] = isset($d['im_collections']) ? $d['im_collections'] : array();
    
    $instance = new _action($types->landing_id);
    foreach ($d['im_books'] as $key => $book_id) {
        $instance->log_impression('story',$book_id);
    }
    foreach ($d['im_collections'] as $key => $collection_id) {
        $instance->log_impression('collection',$collection_id);
    }
    $instance->log_view($types->type,$types->type_id);
    if (intval($d['lastOpen']) > 0) {
        $t = max(intval($d['lastOpen']),time() - 60);
        $instance->log_notifications($types->type,$types->type_id,$t);
    }
    $notifications = notifications::get();
    return [
        'code' => 1,
        'notifications' => $notifications['notifications'],
        'unread'        => $notifications['unread']
    ];
}
$import = [
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
];
foreach ($import as $filename) {
    require_once(__DIR__ . '/api/' . $filename . '.php');
}
if(! headers_sent() && ! isset($_SESSION) ){ 
    session_start(); 
}
function required_params(...$params){
    foreach($params as $param){
        if (! isset($_POST['data'][$param])){
            echo json_encode(array(
                'code'      => 996
            ));
            exit();
        }
    }
}
function required_login(){
    if (! is_user_logged_in()){
        echo json_encode(array(
            'code'  => 995
        ));
        exit();
    }
}
function required_admin(){
    if (! current_user_can('administrator')){
        echo json_encode(array(
            'code'  => 994
        ));
        exit();
    }
}
$reCAPTCHA_apis = ['login','login_with_code','signup','verify_code','contact','publish_review'];
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
    echo json_encode(array(
        'code'  => 998
    ));
    exit();
}
$placeholder = ctrk_decrypt($_POST['placeholder']);
if ( trim($_POST['placeholder'] ?? '') === "" || !$placeholder ) {
    echo json_encode(array(
        'code'      => 993
    ));
    exit();
}
else {
    $_POST['landing_id'] = $placeholder->landing_id;
}
//Call User Functions
if (! function_exists('api_' . $_POST['action'])){
    echo json_encode(array(
        'code'      => 999
    ));
    exit();
}
$b = call_user_func('api_' . $_POST['action']);
if ($b) {
    echo json_encode($b);
}
exit();