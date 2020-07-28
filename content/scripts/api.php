<?php
function api_poll(){
    function return_code($code,$extra = 0){
        $_return = array(
            'code'			=>	$code,
            'notifications'	=>  notifications::get()
        );
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    $types = _landing::decrypt($_POST['data']['data']);
    if ($types === null){
        return_code(9);
    }
    $_POST['data']['im_books'] = isset($_POST['data']['im_books']) ? $_POST['data']['im_books'] : array();
    $_POST['data']['im_collections'] = isset($_POST['data']['im_collections']) ? $_POST['data']['im_collections'] : array();
    
    $instance = new _action($types->landing_id);
    foreach ($_POST['data']['im_books'] as $key => $book_id) {
        $instance->log_impression('book',$book_id);
    }
    foreach ($_POST['data']['im_collections'] as $key => $collection_id) {
        $instance->log_impression('collection',$collection_id);
    }
    $instance->log_view($types->type,$types->type_id);
    // if ($_POST['data']['notification_open'] === 'true'){
    //     $instance->log_notifications($types->type,$types->type_id);
    // }
    return_code(1);
}
$import = [
    'author',
    'chapter',
    'collections',
    'inbox',
    'login',
    'reviews',
    'search-main'
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
$reCAPTCHA_apis = ['login','login_with_code','signup','verify_code'];
if ( in_array($_POST['action'],$reCAPTCHA_apis) ){
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
//Call User Functions
if (! function_exists('api_' . $_POST['action'])){
    echo json_encode(array(
        'code'      => 999
    ));
    exit();
}
call_user_func('api_' . $_POST['action']);
exit();