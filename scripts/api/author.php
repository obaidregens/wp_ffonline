<?php
function api_add_update(){
    $update = $_POST['data']['update'];
    if(! is_user_logged_in()){
        return ['code'=>6];
    }
    if (trim($update) === ''){
        return ['code'=>7];
    }
    $update_id = wp_insert_post(array(
        'post_title'    	=> 'None',
        'post_content'  	=> $update,
        'post_status'   	=> 'publish',
    ));
    $inst = new notifications_insert;
    $inst->addUpdate($update_id);
    return ['code'=>1];
}
function api_update_action(){
    $action = $_POST['data']['action'];
    $update = get_post($_POST['data']['update_id']);
    if ($update === null || $update->post_type !== 'post' || $update->post_status !== 'publish'){
        return ['code'=>7];
    }
    if( intval(get_current_user_id()) !== intval($update->post_author) ){
        return ['code'=>8];
    }
    if (! in_array($action,array('delete','pin'))){
        return ['code'=>9];
    }
    // Actions
    if ($action === 'pin'){
        $pinned = (new WP_Query(array(
            'author__in'        => array($update->post_author),
            'post__in'          => get_option( 'sticky_posts' ),
            'fields'            => 'ids',
            'posts_per_page'    => -1,
        )))->posts;
        foreach ($pinned as $update_id ) {
            if ($update_id !== $update->ID){
                unstick_post( $update_id );
            }
        }
        ! is_sticky($update->ID) ? stick_post( $update->ID ) : unstick_post( $update->ID );
        return ['code'=>1];
    }
    else if ($action === 'delete'){
        wp_update_post( array(
            'ID'            => $update->ID,
            'post_status'   => 'trash'
        ));
        return ['code'=>2];
    }
}
function api_update_bio() {
    required_params('user','bio');
    $user = intval($_POST['data']['user']);
    $bio = $_POST['data']['bio'];
    $is_current_author = intval(get_current_user_id()) === $user;
    if(! $is_current_author ){
        return ['code'=>6];
    }
    update_user_meta( $user, 'description', $bio);
    return ['code'=>1];
}
function api_change_password () {
    required_login();
    required_params('pass','confirm_pass');
    $user = user::get( get_current_user_id() );
    $d = &$_POST['data'];
    $pass = $d['pass'];
    $confirm_pass = $d['confirm_pass'];
    if ($pass !== $confirm_pass) {
        return ['code'=>8];
    }
    $verified = (new v_user(array(
        'password'  => $pass
    ),['password']))->return;
    if (err::is($verified)){
        return ['code'=>9];
    }
    wp_set_password( $pass, $user->ID );
    user::login($user->user_login,$pass);
    return ['code'=>1];
}
function api_change_username() {
    required_login();
    required_params('new_username');
    $new_username = $_POST['data']['new_username'];
    $current_user = user::get(get_current_user_id());
    if ($current_user->user_login === $new_username) {
        return [
            'code'      => 10,
            'errors'    => [
                'username'  => 'What\'s the new username?'
            ]
        ];
    }
    $v = user_settings::change_username($new_username);
    if (err::is($v)) {
        return [
            'code'          => 10,
            'errors'        => array_column($v->errors,'error','name')
        ];
    }
    wp_cache_delete($current_user->user_login, 'userlogins');
    user::internal_login($current_user->ID);
    return ['code'  => 1];
}
function api_follow_user() {
    required_login();
    required_params('user_id');
    $user = user::get( $_POST['data']['user_id'] );
    if (! $user) {
        return ['code'  => 9];
    }
    if (is_current_user($user->ID)) {
        return ['code'  => 10];
    }
    $exists = follow::exists('user',$user->ID);
    if ($exists === false) {
        follow::new([
            'type'      => 'user',
            'type_id'   => $user->ID,
            'landing_id'=> $_POST['landing_id']
        ]);
        return ['code'=>1];
    }
    follow::unfollow('user',$user->ID);
    return ['code'=>2];
}
function api_user_settings() {
    required_login();
    required_params('setting',"set");
    $s = $_POST['data']['setting'];
    $b = $_POST['data']['set'];
    if ($s === "news") {
        $admin = user::get_by( 'login', 'admin' )->ID;
        if ($b) {
            follow::new([
                'type'      => 'user',
                'type_id'   => $admin,
                'landing_id'=> $_POST['landing_id']
            ]);
            return ['code'=>1,'set'=>true];
        }
        follow::unfollow('user',$admin);
        return ['code'=>1,'set'=>false];
    }
    if (!in_array($s,['features'])) {
        return ['code'=>10,'set'=>!$b];
    }
    if (!is_bool($b)) {
        return ['code'=>9,'set'=>!$b];
    }
    user_settings::set($s,$b);
    return ['code'=>1,'set'=>$b];
}