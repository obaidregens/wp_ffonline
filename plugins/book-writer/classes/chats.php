<?php
class chats {
    function __construct($message,$to) {
        $this->error = new err();
        $to = (int) $to;
        $current_user_id = (int) get_current_user_id();
        if ($to === $current_user_id){
            $error->add('to','User cannot message himself.');
        }
        $user = get_user_by( 'ID', $to );
        if ($user === false) {
            $this->error->add('login','Invalid User ID passed.');
        }
        $max_chars = 150;
        if (strlen($message) > $max_chars){
            $this->error->add('message','Message cannot be of more than ' . $max_chars . ' characters.');
        }
        if (chats::is_chat_blocked(array($current_user_id,$to))) {
            $this->error->add('to','Chat is blocked.');
        }
        if ( $this->error->has() ) {
            return $this->error;
        }
        $args = array(
            'post_title'    	=> 'None',
            'post_content'  	=> htmlspecialchars($message),
            'post_status'   	=> 'publish',
            'post_type'			=> 'message',
        );
        $post_id = wp_insert_post($args);
        wp_set_object_terms($post_id,'Sent', 'message_status');
        wp_set_object_terms($post_id, array_unique(array( strval($current_user_id) , strval($to) )), 'message_between');
        $this->ID = $post_id;
    }
    public static function query($args) {
        $error = new err();
        $tax_query = array();
        $date_query = array(array());
        $fields = array(
            'message_status'       => 'status',
            'message_between'      => 'users'
        );
        foreach ( $fields as $tax => $field ) {
            $opers = array(
                'AND'        => 'included',
                'NOT IN'     => 'excluded'
            );
            foreach ($opers as $oper => $oper_arg) {
                $args[$field . '_' . $oper_arg] = $args[$field . '_' . $oper_arg] ?? array();
                $arg = (array) $args[$field . '_' . $oper_arg];
                if (! empty($arg)){
                    $tax_query[] = array(
                        'taxonomy'         => $tax,
                        'terms'            => $arg,
                        'field'            => 'name',
                        'operator'         => $oper,
                        'include_children' => false,
                    );
                }        
            }
        }
        if (isset($args['date']['from'])){
            $date_query[0]['after'] = $args['date']['from'];
        }
        if (isset($args['date']['to'])){
            $date_query[0]['before'] = $args['date']['to'];
        }
        // WP_Query arguments
        $query_args = array(
        	'post_type'              => array( 'message' ),
        	'post_status'            => array( 'publish' ),
            'tax_query'              => $tax_query,
            'date_query'             => $date_query,
        	'order'                  => $args['order'] ?? 'DESC',
        	'orderby'                => $args['orderby'] ?? 'date',
            'posts_per_page'		 => $args['limit'] ?? 50,
            'page'                   => $args['page'] ?? 1,
            'fields'                 => $args['fields'] ?? 'all'
        );
        if (isset($args['author__not_in'])){
            $query_args['author__not_in'] = $args['author__not_in'];
        }
        if (isset($args['author__in'])){
            $query_args['author__in'] = $args['author__in'];
        }
        // The Query
        $query = new WP_Query( $query_args );
        return $query->posts;
    }
    public static function with($protocol = 'all'){
        $current_user_id = get_current_user_id();
        $unread_chats = array_column(chats::query(array(
            'limit'                     => -1,
            'users_included'            => array( $current_user_id ),
            'author__not_in'            => array( $current_user_id ),
            'status_included'           => array('Sent'),
        )),'post_author');
        $unread = array_count_values($unread_chats);
        $all_chats = chats::query(array(
            'limit'             => -1,
            'users_included'    => array( $current_user_id ),
            'fields'            => 'ids',
        ));
        $all_users = array();
        if (! empty($all_chats)){
            $all_users = (new WP_Term_Query(array(
                'object_ids'    => $all_chats,
                'orderby'       => 'include',
                'taxonomy'      => 'message_between',
                'fields'        => 'all_with_object_id',
            )))->terms;
        }
        array_multisort( array_column($all_users, "object_id"), SORT_DESC, $all_users );
        $all_users = array_unique(array_column($all_users,'name'));

        $current_user_index = array_search(strval( $current_user_id ),$all_users);
        if ($current_user_index !== false){
            unset($all_users[$current_user_index]);
        }

        $users = array();
        if (! empty($all_chats)){
            $users = get_users(array(
                'include'       => $all_users,
                'fields'        => array('ID','user_login','display_name'),
                'orderby'       => 'include'
            ));
        }
        foreach ($users as $key => $user ) {
            $users[$key]->unread = $unread[$user->ID] ?? 0;
            $users[$key]->username = $user->user_login;
            $users[$key]->name = $user->display_name;
            unset($users[$key]->user_login);
            unset($users[$key]->display_name);
            if ($users[$key]->unread <= 0 && $protocol === 'new-only'){
                unset($users[$key]);
            }
        }
        return $users;
    }
    public static function block($login){
        $error = new err();
        $user = get_user_by( 'login', $login );
        if ($user === false){
            $error->add('login','Invalid Username passed.');
            return $error;
        }
        $current_user_id = intval(get_current_user_id());
        $user_id = intval($user->ID);
        $block_list = get_user_meta($current_user_id,'block_list',true);
        if ($block_list === ''){
            update_user_meta( $current_user_id,'block_list',array($user_id) );
            return true;
        }
        $block_list[] = $user_id;
        update_user_meta( $current_user_id ,'block_list', array_unique($block_list) );
        return true;
    }
    public static function unblock($login){
        $error = new err();
        $user = get_user_by( 'login', $login );
        if ($user === false){
            $error->add('login','Invalid Username passed.');
            return $error;
        }
        $current_user_id = intval(get_current_user_id());
        $user_id = intval($user->ID);
        $block_list = get_user_meta($current_user_id,'block_list',true);
        if ($block_list === ''){
            return true;
        }
        $index_x = array_search($user_id,$block_list);
        if ($index_x !== false){
            unset($block_list[$index_x]);
            sort($block_list);
        }
        update_user_meta( $current_user_id , 'block_list', array_unique($block_list) );
        return true;
    }
    public static function is_blocked($user_id,$by_user_id = null){
        $error = new err();
        if ($by_user_id === null){
            $by_user_id = get_current_user_id();
        }
        $user_id = (int) $user_id;
        $by_user_id = (int) $by_user_id;

        $by_user = get_user_by( 'ID', $by_user_id );
                
        if ($by_user === false){
            $error->add('by_user_id(1)','Invalid User ID passed: ' . $by_user_id);
        }
        $user = get_user_by( 'ID', $user_id );
        if ($user === false){
            $error->add('user_id(0)','Invalid Username passed: ' . $user_id);
        }
        if ($error->has()){
            return $error;
        }
        $block_list = get_user_meta($by_user_id,'block_list',true);
        if (is_array($block_list) && in_array($user_id,$block_list)){
            return true;
        }
        return false;
    }
    public static function is_chat_blocked($user_ids){
        $error = new err();
        $return = false;
        if (chats::is_blocked($user_ids[0],$user_ids[1]) === true){
            $return = true;
        }
        else if (chats::is_blocked($user_ids[1],$user_ids[0]) === true){
            $return = true;
        }
        return $return;
    }
}
