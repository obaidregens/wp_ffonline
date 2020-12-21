<?php
function api_get_chat(){
    required_login();
    if (($_POST['data']['username'] ?? "") === "") {
        return [
            'username'      => "",
            'messages'      => [],
            'blocked'       => false,
            'chat_blocked'  => false
        ];
    }
    $username = $_POST['data']['username'];
    if (substr($username,0,1) === "@") {
        $username = substr($username,1);
    }

    $user = user::get_by( 'login', $username );
    if ($user === false) {
        return ['code'=>8];
    }
    $users_in_chat = array(
        intval( get_current_user_id() ),
        intval( $user->ID )
    );
    if ($users_in_chat[0] === $users_in_chat[1]){
        return ['code'=>9];
    }
    $chats = chats::query([
        'users_included'  => $users_in_chat,
        'order'           => 'ASC',
        'per_page'        => -1
    ]);
    $set_to_read = [];
    $chats_f = [];
    $first_unread = false;
    foreach ( $chats as $chat ) {
        $chat_author = intval($chat->from);
        $this_chat_obj = [
            'ID'        => $chat->ID,
            'from'      => $users_in_chat[0] === $chat_author ? 'my' : 'other',
            'time'      => $chat->milli_timestamp,
            'message'   => $chat->message,
        ];
        if ($this_chat_obj['from'] === 'other' && $chat->status === 'sent' && !$first_unread ){
            $this_chat_obj['new'] = true;
            $first_unread = true;
        }
        $chats_f[] = $this_chat_obj;
        if ($chat_author !== $users_in_chat[0] && $chat->status === 'sent' ){
            $set_to_read[] = $chat->ID;
        }
    }
    if (! empty($set_to_read)) {
        global $wpdb;
        $wpdb->query($wpdb->prepare("
        UPDATE chats
        SET status = 'read'
        WHERE ID IN(" . implode(',',array_fill(0,count($set_to_read),'%s')) . ")
        ",$set_to_read));    
    }
    
    return [
        'username'      => "@".$user->user_login,
        'messages'      => $chats_f,
        'blocked'       => chats_blocking::is_blocked($users_in_chat[1]),
        'chat_blocked'  => chats_blocking::is_chat_blocked($users_in_chat)
    ];
}
function api_chats_with() {
    return ['with' => chats::with()];
}
function api_send_message(){
    required_login();
    required_params('message','to');
    $username = $_POST['data']['to'];
    if (substr($username,0,1) === "@") {
        $username = substr($username,1);
    }
    $user = user::get_by( 'login', $username );
    if ($user === false){
        return ['sent'=>false];
    }
    // Add the content of the form to $post as an array
    $chat_obj = new chats($_POST['data']['message'],$user->ID);
    $success = true;
    if ($chat_obj->error->has()){
        $success = false;
    }
    return ['sent'=>$success];
}
function api_block () {
    required_login();
    required_params('block','username');
    $username = $_POST['data']['username'];
    if (substr($username,0,1) === "@") {
        $username = substr($username,1);
    }
    $block = (bool) $_POST['data']['block'];
    $user = user::get_by( 'login', $username );
    if (!$user) {
        return ['code'=>9];
    }
    $block ?
        chats_blocking::block($user->user_login) :
        chats_blocking::unblock($user->user_login);
    
    return ['code'  => 1];
}