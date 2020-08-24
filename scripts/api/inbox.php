<?php
function api_get_chat(){
    required_login();
    required_params('username');

    $user = get_user_by( 'login', $_POST['data']['username'] );
    if ($user === false) {
        return ['messages'=>false];
    }
    $users_in_chat = array(
        intval( get_current_user_id() ),
        intval( $user->ID )
    );
    if ($users_in_chat[0] === $users_in_chat[1]){
        return ['messages'=>false];
    }
    $chats = chats::query(array(
        'users_included'  => $users_in_chat
    ));
    $read_chats = chats::query(array(
        'limit'             => -1,
        'users_included'    => $users_in_chat,
        'status_included'   => array('Read'),
        'fields'            => 'ids'
    ));
    $chats_f = array();
    foreach ( $chats as $chat ) {
        $chat_author = intval($chat->post_author);
        $this_chat_obj = array(
            'from'      => $users_in_chat[0] === $chat_author ? 'my' : 'other',
            'date'      => $chat->post_date,
            'message'   => $chat->post_content
        );
        if ($this_chat_obj['from'] === 'my'){
            // $this_chat_obj['status'] = in_array($chat->ID,$read_chats) ? 'read' : 'sent';
        }
        $chats_f[] = $this_chat_obj;
        if ($chat_author !== $users_in_chat[0] && ! in_array($chat->ID,$read_chats)){
            wp_set_object_terms($chat->ID,'Read', 'message_status');            
        }
    }
    return [
        'user'          => array(
                'name'      => $user->display_name
        ),
        'messages'      => array_reverse($chats_f),
        'blocked'       => chats_blocking::is_blocked($users_in_chat[1]),
        'chat_blocked'  => chats_blocking::is_chat_blocked($users_in_chat)
    ];
}
function api_send_message(){
    required_login();
    required_params('message','to');
    $user = get_user_by( 'login', $_POST['data']['to'] );
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
    $user = get_user_by( 'login', $_POST['data']['username'] );
    $_POST['data']['block'] === "true" ? chats_blocking::block($user->user_login) : chats_blocking::unblock($user->user_login);
}