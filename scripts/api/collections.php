<?php
function api_get_collections() {
    required_login();
    required_params('book_ids');
    return [
        'code'              => 4,
        'collections_data'  => collection_helpers::js_data(),
        'book_collections'  => collection_helpers::query_by_book( $_POST['data']['book_ids'] ?? [] )
    ];
}
function api_update_collection(){
    required_login();
    required_params('to_delete','collection_id','title','privacy');
    $d = &$_POST['data'];
    $collection_id = $d['collection_id'];
    if ($d['to_delete'] === true){
        collection::delete($collection_id);
        if ($collection_id === 'new'){
            return ['code'=>10];
        }
        return [
            'code'              => 2,
            'collections_data'  => collection_helpers::js_data(),
            'notice'            => "Collection Deleted"
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
        'notice'            => $collection_id === "new" ? "Collection Created" : "Collection Updated",
        'collections_data'  => collection_helpers::js_data()
    ];
}
function api_add_to_collection(){
    required_login();
    required_params('book_id','collection_id','add');
    $d = &$_POST['data'];
    $story = story::get($d['book_id'],false);
    if (! $story) {
        return ['code'=>7];
    }

    $add = $d['add'] === true;
    $collection = collection::get_by('ID',$d['collection_id']);
    if (!$collection || !is_current_user($collection->author)) {
        return [
            'code' => 10,
        ];
    }
    ob_start();
    $return = $add ?
        collection_books::add($d['collection_id'],$d['book_id']) :
        collection_books::remove($d['collection_id'],$d['book_id']);
    ob_end_clean();
    return [
        'code'              => 1,
        'book_collections'  => collection_helpers::query_by_book([$d['book_id']]),
        'is_hidden'         => $add && $collection->title === "Hidden"
    ];
}
function api_follow_collection(){
    required_login();
    required_params('collection_id','follow');
    $follow = $_POST['data']['follow'] === true;
    $collection = collection::get_by('ID',$_POST['data']['collection_id']);
    if (!$collection) {
        return ['code'=>9];
    }
    if (!is_current_user($collection->author) && $collection->type === "Private") {
        return ['code'=>10];
    }
    $collection_id = $collection->ID;
    if ($follow) {
        collection_follow::follow( $collection_id, get_current_user_id(),landing_id() );
        return ['code' => 1];
    }
    collection_follow::unfollow( $collection_id, get_current_user_id() );
    return ['code' => 2];
}