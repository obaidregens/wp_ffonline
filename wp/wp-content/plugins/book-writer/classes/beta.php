<?php
class beta {
    protected static $table = "beta_sessions";
    protected static $user_table = "beta_users";
    static function can($user_id = null) {
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
        $r = $wpdb->get_results($wpdb->prepare($sql,[millitime(),$current]));
        return ( !empty($r) ) || current_user_can( 'administrator' );    
    }
    static function is () {
        global $app;
        return $app->is_beta();
    }
    static function new_session(array $args) {
        $args = array_replace([
            'description'       => "",
            'users'             => 15,
            "custom"            => [],
            'duration'          => 1000*1*60*60*12 //12 hours
        ],$args);
        if (trim($args['description']) === "") {
            return (new err)->add('description',"Description can't be empty");
        }
        if (strlen($args['description']) > 400) {
            return (new err)->add('description',"Description can't be of more than 400 characters.");
        }
        if (intval($args['users']) < 1) {
            return (new err)->add("users","Can't create session with no users.");
        }
        if (self::get_current()) {
            return (new err)->add("current","Session in progress");
        }
        $milli = millitime();
        global $wpdb;
        $wpdb->insert(
            self::$table,[
                'description'  => $args['description'],
                'start_time'   => $milli,
                'end_time'     => $milli+intval($args['duration'])
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
        $r = $wpdb->get_results(
            "SELECT ID FROM wp_users
            WHERE user_status = 0
            ORDER BY RAND()
            LIMIT $num;"
        );
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