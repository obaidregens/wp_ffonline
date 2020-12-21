<?php
class change_logs {
    protected static $table = "change_logs";
    static function new ($type,$old_value,$new_value,$landing_id,$user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        global $wpdb;
        $wpdb->insert(
            self::$table,[
                'user_id'       => $user_id,
                'type'          => $type,
                'old_value'     => $old_value,
                'new_value'     => $new_value,
                'landing_id'	=> $landing_id,
                'changed'       => millitime()
            ]
        );
    }
}