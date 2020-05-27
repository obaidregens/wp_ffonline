<?php

function log_stats($stat,$type,$type_id,$vfs){
    $types_all = array('book','home','page','chapter','collection','author','survey');
    $stats_all = array('impression','view','submit');
    if ( in_array($type,array('book','chapter','home','page')) ){
        $type_obj = get_post($type_id);
    }
    else if ( in_array($type,array('collection')) ){
        $type_obj = get_term($type_id,'collection');
    }
    else if ( in_array($type,array('author')) ){
        $type_obj = get_userdata($type_id);
    }
    else if ( in_array($type,array('survey')) ){
        $type_obj = get_surveys(array(
            'ids'   => array($type_id)
        ));
        if (empty($type_obj)){
            $type_obj = false;
        }
        else{
            $type_obj = $type_obj[0];
        }
    }
    if (! in_array($type,$types_all) ||
        ! $type_obj ||
        ! in_array($stat,$stats_all) ||
        ($type == 'chapter' && $type_obj->post_type != 'chapter') ||
        ($type == 'book' && $type_obj->post_type != 'book') ||
        ($stat == 'impression' && ! in_array($type,array('collection','book')) ) ||
        ($stat == 'submit' && ! in_array($type,array('survey')) ) ||
        ($type == 'survey' && ! in_array($stat,array('submit')) )
        ){
        return false;
    }
    //Errors Handled
    $table_name = 'custom_stats';
    $referrer = get_referrer();
    $input_data = array(
        'type'              => $type,
        'type_id'           => $type_id,
        'timestamp'         => current_time('timestamp',true),
        'stat'              => $stat,
        'user_id'           => get_current_user_id(),
        'IP'                => $_SERVER['REMOTE_ADDR'],
        'cookie_id'         => $vfs,
        'referrer_host'     => $referrer['host'],
        'referrer_path'     => $referrer['path']
    );
    global $wpdb;
    $wpdb->insert(
        $table_name,
        $input_data
    );
    return true;
}


function set_cookie($vfs){
    setcookie('vfs', $vfs, time() + (86400 * 5475), "/");
}
function vfs(){
    $table_name = 'custom_stats';
    global $wpdb;
    if( isset($_COOKIE['vfs'])) {
        $result = $wpdb->get_results( $wpdb->prepare( "
            SELECT * FROM $table_name WHERE cookie_id = %s LIMIT 1
        ",$_COOKIE['vfs']));
        if (! is_null($result) && ! empty($result)){
            $vfs = $result[0]->cookie_id;
            set_cookie($vfs);
            return $vfs;
        }
    }
    if (is_user_logged_in()){
        $result = $wpdb->get_results( $wpdb->prepare( "
            SELECT * FROM $table_name WHERE user_id = %d LIMIT 1
        ",get_current_user_id()));
        if (! is_null($result) && ! empty($result)){
            $vfs = $result[0]->cookie_id;
            set_cookie($vfs);
            return $vfs;
        }
    }
    $result = $wpdb->get_results( $wpdb->prepare( "
        SELECT * FROM $table_name WHERE IP = %s LIMIT 1
    ",$_SERVER['REMOTE_ADDR']));
    if (! is_null($result) && ! empty($result)){
        $vfs = $result[0]->cookie_id;
        set_cookie($vfs);
        return $vfs;
    }
    $vfs = bin2hex(random_bytes(39));
    set_cookie($vfs);
    return $vfs;
}
function get_referrer(){
    if (! isset($_SERVER['HTTP_REFERER'])){
        return array(
            'host'  => null,
            'path'  => null
        );
    }
    $referrer = parse_url($_SERVER['HTTP_REFERER']);
    if ($referrer['host'] == 'fanfiction.online' || $referrer['host'] == '192.168.100.33'){
        $referrer['host'] = 'local';
    }
    return $referrer;
}
