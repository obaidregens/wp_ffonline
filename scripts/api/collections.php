<?php
function api_add_to_collection(){
    required_login();
    $_POST['data'] = json_decode(str_replace('\\','',$_POST['data']['data_']),true);
    function return_code($code,$extra = 0){
        $_return = array(
            'code'				=>	$code,
        );
        if ($code <= 5){
            $_return['book_collections'] = collection::query_by_book($_POST['data']['book_ids'],'ID');
        }
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    if (! is_user_logged_in()){
        return_code(6);
    }
    $_POST['data']['book_ids'] = array_keys($_POST['data']['book_collections']);
    foreach ($_POST['data']['book_ids'] as $key => $book_id) {
        $return = collection::set('book',$book_id,$_POST['data']['book_collections'][$book_id],null,get_current_user_id());
        if (err::is($return) ){
            return_code(7);
        }
    }
    return_code(1);
}
function api_update_collection(){
    required_login();
    function return_code($code){
        $_return = array(
            'code'			=> $code,
        );
        if ($code === 1 || $code === 2){
            $_return['collections_data'] = collection::js_data();
            $_return['book_collections'] = collection::query_by_book( $_POST['data']['book_ids'], 'ID' );
        }
        echo json_encode($_return);
        exit();
    }
    $collection_id = $_POST['data']['collection_id'];
    if ($_POST['data']['delete'] === "true"){
        $return = collection::update($collection_id,array(
            'type'                 => 'Trash'
        ));
        if (err::is($return) || $collection_id === 'new'){
            return_code(10);
        }
        return_code(2);
    }
    $title = $_POST['data']['title'];
    if (strlen($title) > 50){
        return_code(7);
    }
    $privacy = $_POST['data']['privacy'];
    if (! in_array($privacy,array('Public','Private','Unlisted'))){
        return_code(8);
    }
    $arguments = array(
        'title' => $title,
        'type'  => $privacy
    );
    if ($collection_id === 'new'){
        $return_collection = collection::create($arguments);
    }
    else{
        $return_collection = collection::update(intval($collection_id),$arguments);
    }
    if (err::is($return_collection)){
        return_code(9);
    }
    return_code(1);
}
function api_follow_collection(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    required_login();
    required_params('collection_id','follow');
    $follow = $_POST['data']['follow'] === "true";
    $collection_id = $_POST['data']['collection_id'];
    $return = $follow ? collection_follow::follow($collection_id) : collection_follow::unfollow($collection_id);
    if (err::is($return)){
        return_code(8);
    }
    return_code($follow ? 1 : 2);
}