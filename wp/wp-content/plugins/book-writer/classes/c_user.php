<?php
class c_user {
    public static $table = 'user_connections';
    function __construct($args){
        $table = self::$table;
        $args = array_replace(array(
            'user_id'              => get_current_user_id(),
            'connection_from'      => 'ffn'
        ),$args);
        if (! isset($args['connection_user'])){
            $error = new err();
            $error->add('connection_user','None specified');
            $this->error = $error;
            return;
        }
        $args['status'] = 'unverified';
        global $wpdb;
        $connected = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
                WHERE connection_user = %s
                AND connection_from = %s
                AND status = %s",
            array($args['connection_user'],$args['connection_from'],'verified')
        ));
        if (! empty($connected)){
            return;
        }
        $existing = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
                WHERE connection_user = %s
                AND connection_from = %s
                AND status IN (%s,%s)
                AND user_id = %d",
            array($args['connection_user'],$args['connection_from'],'unverified','verified',$args['user_id'])
        ));
        if (! empty($existing)){
            if ($existing[0]->status === 'verified'){
                return;
            }
            v_code::delete($existing[0]->verification_ID);
            $new_v = new v_code();
            $wpdb->update(
                $table,
                array(
                    'verification_ID'   => $new_v->ID
                ),
                array(
                    'ID'    => $existing[0]->ID
                )
            );
            $this->ID = $existing[0]->ID;
            $this->code = $new_v->code;
            return;
        }
        $verification = new v_code();
        $args['verification_ID'] = $verification->ID;
        $wpdb->insert(
            $table,
            $args
        );
        $this->ID = $wpdb->insert_id;
        $this->code = $verification->code;
    }
    protected static function verify($connection_user,$code,$connection_from = 'ffn'){
        $table = self::$table;
        $error = new err();
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
                WHERE connection_user = %s
                AND connection_from = %s
                AND status = %s",
            array($connection_user,$connection_from,'unverified')
        ));
        if (empty($results)){
            return $error->add('Connection','None Exists');
        }
        if (! $results[0]->verification_ID){
            return $error->add('Verification Code','None exists');
        }
        $is_valid = v_code::is($results[0]->verification_ID,$code);
        if (! $is_valid){
            return $error->add('Verification Code','Invalid');
        }
        v_code::delete($results[0]->verification_ID);
        $wpdb->update(
            $table,
            array(
                'status'   => 'verified'
            ),
            array(
                'ID'    => $results[0]->ID
            )
        );
    }
    public static function get($connection_user, $connection_from = 'ffn') {
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM user_connections WHERE status = 'verified' AND connection_user = %s AND connection_from = %s",
            [$connection_user,$connection_from]
        ));
        if (empty($r)){
            return false;
        }
        return empty($r) ? false : intval($r[0]->user_id);

    }
    public static function current ($user = null) {
        if ($user === null){
            if (! is_user_logged_in() ){
                return false;
            }
            $user = get_current_user_id();
        }
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM user_connections WHERE status = 'verified' AND user_id = %s",
            [$user]
        ));
        return empty($r) ? false : $r[0]->connection_user;
    }
    static function pending($user = null) {
        if ($user === null){
            if (! is_user_logged_in() ){
                return false;
            }
            $user = get_current_user_id();
        }
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM user_connections WHERE status = 'unverified' AND user_id = %s ORDER BY ID DESC",
            [$user]
        ));
        return empty($r) ? false : $r[0]->connection_user;
    }

}