<?php
class chats {
    public static $table = 'chats';
    function __construct($message,$to) {
        $this->error = new err();
        $to = (int) $to;
        $from = (int) get_current_user_id();
        if ($from === 0) {
            return $this->error->add('user','No one is logged in.');
        }
        if ($to === $from){
            return $this->error->add('to','User cannot message himself.');
        }
        $user = get_user_by( 'ID', $to );
        if ($user === false) {
            return $this->error->add('to','Invalid User ID passed.');
        }
        if (trim($message) === ''){
            return $this->error->add('Message','What message?');
        }
        $max_chars = 150;
        if (strlen($message) > $max_chars){
            return $this->error->add('Message',"Message cannot be of more than $max_chars characters.");
        }
        if (chats_blocking::is_chat_blocked(array($from,$to))) {
            return $this->error->add('to','Chat is blocked.');
        }
        if ( $this->error->has() ) {
            return $this->error;
        }
        global $wpdb;
        $wpdb->insert(
            self::$table,
            [
                'from'              => $from,
                'to'                => $to,
                'status'            => 'sent',
                'message'           => $message,
                'milli_timestamp'   => intval(microtime(true)*1000)
            ],
        );
        $this->ID = $wpdb->insert_id;
    }
    public static function query($args) {
        $error = new err();
        $opers = [
            'IN'        => 'included',
            'NOT IN'    => 'excluded'
        ];
        $table = self::$table;
        $sql = "SELECT * FROM $table WHERE 1";
        $prep = [];
        foreach ($opers as $oper => $oper_arg) {
            // Users
            $k = &$args['users_' . $oper_arg];
            $k = (array) ($k ?? []);
            if (!empty($k)) {
                $placeholder = implode(',',array_fill(0,count($k),'%s'));
                $sql .= " AND (
                    `from` $oper(" . $placeholder . ") OR
                    `to` $oper(" . $placeholder . ")
                )
                ";
                $prep = array_merge($prep,$k,$k);    
            }

            $fields = ['status','from'];
            foreach ($fields as $field ) {
                $k = &$args[$field . '_' . $oper_arg];
                $k = (array) ($k ?? []);
                if (!empty($k)) {
                    $placeholder = implode(',',array_fill(0,count($k),'%s'));
                    $sql .= " AND (
                        `$field` $oper(" . $placeholder . ")
                    )
                    ";
                    $prep = array_merge($pre,$k); 
                }    
            }
        }
        if (isset($args['date']['from'])){
            $sql .= " AND milli_timestamp >= %d";
            $prep[] = $args['date']['from']*1000;
        }
        if (isset($args['date']['to'])){
            $sql .= " AND milli_timestamp <= %d";
            $prep[] = $args['date']['to']*1000;
        }
        $args['order'] = in_array($args['order'] ?? 'M',['DESC','ASC']) ? $args['order'] : 'DESC';
        $sql .= ' ORDER BY milli_timestamp ' . $args['order'];
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,$prep));
        if ( ($args['per_page'] ?? 0) === -1 ) {
            return $r;
        }
        return array_slice(
            $r,
            ( ($args['page'] ?? 1) -1)*($args['per_page'] ?? 50),
            $args['per_page'] ?? 50
        );
    }
    public static function with(){
        $table = self::$table;
        $current_user_id = intval(get_current_user_id());
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE `from` = %s OR `to` = %s",[$current_user_id,$current_user_id]);
        $messages = $wpdb->get_results($sql);
        $usernames = array_column($wpdb->get_results("SELECT ID,user_login FROM wp_users"),'user_login','ID');

        $users = [];
        foreach ($messages as $row ) {
            $user_id = array_values(array_diff([intval($row->from),intval($row->to)],[$current_user_id]))[0];
            $users[$user_id] = $users[$user_id] ?? [
                'ID'        => $user_id,
                'username'  => "@" . $usernames[strval($user_id)],
                'unread'    => 0,
            ];
            if ($row->status === 'sent'){
                $users[$user_id]['unread'] += 1;
            }
        }
        return array_values($users);
    }
}
class chats_blocking extends chats {
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
        if (self::is_blocked($user_ids[0],$user_ids[1]) === true){
            $return = true;
        }
        else if (self::is_blocked($user_ids[1],$user_ids[0]) === true){
            $return = true;
        }
        return $return;
    }
}