<?php
function api_add_update(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code,
        ));
        exit();
    }
    $update = $_POST['data']['update'];
    if(! is_user_logged_in()){
        return_code(6);
    }
    if (trim($update) === ''){
        return_code(7);
    }
    wp_insert_post(array(
        'post_title'    	=> 'None',
        'post_content'  	=> htmlspecialchars($update),
        'post_status'   	=> 'publish',
    ));
    return_code(1);
}
function api_update_action(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code,
        ));
        exit();
    }
    $action = $_POST['data']['action'];
    $update = get_post($_POST['data']['update_id']);
    if ($update === null || $update->post_type !== 'post' || $update->post_status !== 'publish'){
        return_code(7);
    }
    if( intval(get_current_user_id()) !== intval($update->post_author) ){
        return_code(8);
    }
    if (! in_array($action,array('delete','pin'))){
        return_code(9);
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
        return_code(1);
    }
    else if ($action === 'delete'){
        wp_update_post( array(
            'ID'            => $update->ID,
            'post_status'   => 'trash'
        ));
        return_code(2);
    }
}
function api_update_bio() {
    function return_code($code){
        echo json_encode(array(
            'code'  => $code,
        ));
        exit();
    }
    required_params('user','bio');
    $user = intval($_POST['data']['user']);
    $bio = $_POST['data']['bio'];
    $is_current_author = intval(get_current_user_id()) === $user;
    if(! $is_current_author ){
        return_code(6);
    }
    update_user_meta( $user, 'description', htmlspecialchars($bio));
    return_code(1);
}
function api_change_password () {
    required_login();
    required_params('pass','confirm_pass');
    function return_code($code) {
        echo json_encode([
            'code'      => $code
        ]);
        exit();
    }
    $user = get_userdata( get_current_user_id() );
    $d = &$_POST['data'];
    $pass = $d['pass'];
    $confirm_pass = $d['confirm_pass'];
    if ($pass !== $confirm_pass) {
        return_code(8);
    }
    $verified = (new v_user(array(
        'password'  => $pass
    ),['password']))->return;
    if (err::is($verified)){
        return_code(9);
    }
    wp_set_password( $pass, $user->ID );
    user::login($user->user_login,$pass);
    return_code(1);
}