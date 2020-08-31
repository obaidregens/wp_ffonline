<?php
function api_update_collection(){
    required_login();
    required_params('delete','collection_id','title','privacy');
    $d = &$_POST['data'];
    $collection_id = $d['collection_id'];
    if ($d['delete'] === "true"){
        collection::delete($collection_id);
        if ($collection_id === 'new'){
            return ['code'=>10];
        }
        return [
            'code'              => 2,
            'collections_data'  => collection_helpers::js_data(),
            'book_collections'  => collection_helpers::query_by_book( $d['book_ids'] ?? [] )
        ];
    }
    $title = $d['title'];
    if (trim($title) === '') {
        return ['code' => 11];
    }
    if (strlen($title) > 50){
        return ['code'=>7];
    }
    $privacy = $d['privacy'];
    if (! in_array($privacy,array('Public','Private','Unlisted'))){
        return ['code'=>8];
    }
    $arguments = array(
        'title' => $title,
        'type'  => $privacy
    );

    if ($collection_id !== 'new'){
        $arguments['ID'] = $collection_id;
    }
    $re = collection::update($arguments);
    return [
        'code'              => 1,
        'collections_data'  => collection_helpers::js_data(),
        'book_collections'  => collection_helpers::query_by_book( $d['book_ids'] )
    ];
}
function api_add_to_collection(){
    required_login();
    required_params('book_id','collection_id');
    $d = &$_POST['data'];
    $add = $d['add'] === 'true';
    $return = $add ?
        collection_books::add($d['collection_id'],$d['book_id']) :
        collection_books::remove($d['collection_id'],$d['book_id']);
    return [
        'code'              => 1,
        'book_collections'  => collection_helpers::query_by_book([$d['book_id']])
    ];
}
function api_follow_collection(){
    required_login();
    required_params('collection_id','follow');
    $follow = $_POST['data']['follow'] === "true";
    $collection_id = $_POST['data']['collection_id'];
    if ($follow) {
        collection_follow::follow( $collection_id, get_current_user_id() );
        return ['code' => 1];
    }
    collection_follow::unfollow( $collection_id, get_current_user_id() );
    return ['code' => 2];
}