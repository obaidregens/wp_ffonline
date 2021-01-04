<?php
function api_poll(){
    $landing = landing::now();
    $d = &$_POST['data'];
    $d['im_books'] = array_unique($d['im_books']) ?? [];
    $d['im_collections'] = array_unique($d['im_books']) ?? [];
    
    $instance = new _action($landing->ID);
    if ($instance->error->has()) {
        return ['code'=>993];
    }
    foreach ($d['im_books'] as $key => $book_id) {
        $instance->log_impression('story',$book_id);
    }
    foreach ($d['im_collections'] as $key => $collection_id) {
        $instance->log_impression('collection',$collection_id);
    }
    $instance->log_view($landing->type,$landing->type_id);
    if (intval($d['lastOpen']) > 0) {
        $t = max(intval($d['lastOpen']),time() - 60);
        $instance->log_notifications($landing->type,$landing->type_id,$t);
    }
    if (isset($d['track'])) {
        api_track_chapter();
    }
    $notifications = notifications::get();
    return [
        'code' => 1,
        'notifications' => $notifications['notifications'],
        'unread'        => $notifications['unread'],
        'new_messages'  => chats::unread(),
    ];
}