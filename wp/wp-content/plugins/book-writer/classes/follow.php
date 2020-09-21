<?php
class follow {
    public static $table = 'follows';
    protected static $types = ['collection','user'];
    static function new($args) {
        $e = new err();
        $e->is_required([
            'type',
            'type_id',
            'landing_id'
        ],$args);
        $e->one_of('type',$args['type'],self::$types);
        $e->numeric('type_id',$args['type_id']);
        $e->numeric('landing_id',$args['landing_id']);
        $e->numeric('user_id',$args['user_id'] ?? 0);
        if ($e->has()){

            return $e;
        }
        $args = array_replace([
            'user_id'       => get_current_user_id(),
            'notifications' => "all"
        ],$args);
        $args['followed_time'] = microtime(true);
        ob_start();
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        ob_end_clean();
        return true;
    }
    static function unfollow ($type, $type_id, $user = null) {
        $user = $user === null ? get_current_user_id() : $user;
        global $wpdb;
        $wpdb->delete(
            self::$table,
            [
                'type'            => $type,
                'type_id'         => $type_id,
                'user_id'         => $user
            ]
        );
    }
    static function exists($type, $type_id, $user = null) {
        $user = $user === null ? get_current_user_id() : $user;
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE type = %s AND type_id = %d AND user_id = %d",[$type,$type_id,$user]);
        $r = $wpdb->get_results($sql);
        return empty($r) ? false : intval($r[0]->followed_time);
    }
    static function query_by($type, $field, $value) {
        $e = new err();
        $e->one_of('$type',$type,self::$types ?? []);
        $e->one_of('$field',$field,['type_id','user_id']);
        if ($e->has()) {
            return $e;
        }
        global $wpdb;
        $table = self::$table;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE $field = %s",[$value]);
        $results = $wpdb->get_results($sql);
        return $results;
    }
}