<?php
class vote {
    public static $table = 'votes';
    function __construct($args) {
        $e = new err();
        $e->is_required([
            'type',
            'type_id',
            'landing_id'
        ],$args);
        if ($e->has()){
            return $e;
        }
        $args = array_replace([
            'user_id'       => get_current_user_id(),
        ],$args);
        $args['followed_time'] = microtime(true);
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        return true;
    }
}