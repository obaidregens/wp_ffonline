<?php
function api_add_to_collection(){
    required_login();
    $_POST['data'] = json_decode(str_replace('\\','',$_POST['data']['data_']),true);
    $_POST['data']['book_ids'] = array_keys($_POST['data']['book_collections']);
    foreach ($_POST['data']['book_ids'] as $key => $book_id) {
        $return = collection::set('book',$book_id,$_POST['data']['book_collections'][$book_id],null,get_current_user_id());
        if (err::is($return) ){
            return ['code'=> 7];
        }
    }
    return [
        'code'              => 1,
        'book_collections'  => collection::query_by_book($_POST['data']['book_ids'],'ID')
    ];
}
function api_update_collection(){
    required_login();
    $collection_id = $_POST['data']['collection_id'];
    if ($_POST['data']['delete'] === "true"){
        $return = collection::update($collection_id,array(
            'type'                 => 'Trash'
        ));
        if (err::is($return) || $collection_id === 'new'){
            return ['code'=>10];
        }
        return [
            'code'              => 2,
            'collections_data'  => collection::js_data(),
            'book_collections'  => collection::query_by_book( $_POST['data']['book_ids'], 'ID' )
        ];
    }
    $title = $_POST['data']['title'];
    if (strlen($title) > 50){
        return ['code'=>7];
    }
    $privacy = $_POST['data']['privacy'];
    if (! in_array($privacy,array('Public','Private','Unlisted'))){
        return ['code'=>8];
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
        return ['code'=>9];
    }
    return [
        'code'              => 1,
        'collections_data'  => collection::js_data(),
        'book_collections'  => collection::query_by_book( $_POST['data']['book_ids'], 'ID' )
    ];
}
function api_follow_collection(){
    required_login();
    required_params('collection_id','follow');
    $follow = $_POST['data']['follow'] === "true";
    $collection_id = $_POST['data']['collection_id'];
    $return = $follow ? collection_follow::follow($collection_id) : collection_follow::unfollow($collection_id);
    if (err::is($return)){
        return ['code'=>8];
    }
    return ['code'=>$follow ? 1 : 2];
}