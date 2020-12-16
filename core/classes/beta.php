<?php
class beta {
    protected static $table = "beta_sessions";
    protected static $user_table = "beta_users";
    static function can($user_id = null,$could = 0) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        $current = (int) $user_id;

        $table = self::$table;
        $utable = self::$user_table;
        $sql =
        "SELECT * FROM $utable
        INNER JOIN $table ON $table.ID = $utable.beta_id
        WHERE $table.`end_time` > %d
        AND $utable.`user_id` = %d";

        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[millitime()-$could,$current]));
        return ( !empty($r) ) || current_user_can( 'administrator' );    
    }
    static function expired($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        $current = (int) $user_id;
        $table = self::$table;
        $utable = self::$user_table;
        $sql =
        "SELECT * FROM $utable
        INNER JOIN $table ON $table.ID = $utable.beta_id
        WHERE $utable.`user_id` = %d
        ORDER BY $table.end_time DESC";

        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[$current]));
        return empty($r) ? false : $r[0];    
    }
    static function is () {
        return defined('IS_BETA') && IS_BETA === 'TRUE';
    }
    static function new_session(array $args) {
        $args = array_replace([
            'description'       => "",
            'users'             => 15,
            'start_url'         => "https://fanfiction.online/read",
            "custom"            => [],
            'duration'          => 1000*1*60*60*12 //12 hours
        ],$args);
        if (trim($args['description']) === "") {
            return (new err)->add('description',"Description can't be empty");
        }
        if (strlen($args['description']) > 5000) {
            return (new err)->add('description',"Description can't be of more than 5000 characters.");
        }
        if (intval($args['users']) < 1) {
            return (new err)->add("users","Can't create session with no users.");
        }
        if (self::get_current()) {
            return (new err)->add("current","Session in progress");
        }
        $milli = millitime();
        $end_time = roundToNextHour(($milli+intval($args['duration']))/1000)*1000;
        global $wpdb;
        $wpdb->insert(
            self::$table,[
                'description'  => $args['description'],
                'start_url'    => $args['start_url'],
                'start_time'   => $milli,
                'end_time'     => $end_time
            ]
        );
        $beta_id = intval($wpdb->insert_id);
        $custom = $args['custom'];
        $rand = self::random_draw($args['users'] - count($custom));
        $users = array_merge($custom,$rand);
        $inst = new notifications_insert;
        $db = new db(self::$user_table,[
            'beta_id'   => $beta_id,
            'user_id',
            'selection'
        ]);
        foreach ($users as $user) {
            $inst->inviteToBeta($beta_id,$user);
            $return = $db->insert([
                'user_id'   => $user,
                'selection' => in_array($user,$custom) ? "custom": "automated"
            ]);
        }
        $db->close();
        return $beta_id;
    }
    protected static function random_draw($num) {
        $num = (int) $num;
        if (!is_int($num)) {
            return false;
        }
        global $wpdb;
        $user_ids = array_column($wpdb->get_results(
            "SELECT `user_id` FROM wp_usermeta
            WHERE meta_key = 'usetting_features'
            AND meta_value = 's:4:\"b:0;\";'"
        ),'user_id');
        $sql = $wpdb->prepare(
            "SELECT ID FROM wp_users
            WHERE user_status = 0
            " . (empty($user_ids) ? "" : "AND ID NOT IN (" . sqlPlaceholder($user_ids) . ")") . "
            ORDER BY RAND()
            LIMIT $num;",
            $user_ids
        );
        $r = $wpdb->get_results($sql);
        return array_map('intval',array_column($r,'ID'));
    }
    static function get_current() {
        global $wpdb;
        $table = self::$table;
        $utable = self::$user_table;

        $milli = millitime();
        $sql =
        "SELECT * FROM $table
        INNER JOIN $utable ON $table.ID = $utable.beta_id
        WHERE $table.end_time > %d";
        $sql = $wpdb->prepare($sql,[$milli]);
        $existing = $wpdb->get_results($sql,ARRAY_A);
        if (empty($existing)) {
            return false;
        }
        $r = [];
        foreach($existing as $row) {
            $k = &$r[$row['ID']];
            $k = $k ?? array_replace($row,[
                'users' => []
            ]);
            $k['users'][] = (int) $row['user_id'];
        }
        return array_values($r)[0];
    }
}
