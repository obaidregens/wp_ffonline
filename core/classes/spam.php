<?php
class spam {
    static $table = "spam_log";
    static function add($reason,$landing_id,$desc = "") {
        global $wpdb; 
        $sql = $wpdb->prepare("SELECT * FROM stats_landings WHERE ID = %d",[$landing_id]);
        $r = $wpdb->get_results($sql);
        $wpdb->insert(
            self::$table,
            [
                "reason"            => $reason,
                "user_id"           => get_current_user_id(),
                "IP"                => $_SERVER['REMOTE_ADDR'],
                "landing_id"        => $landing_id,
                "description"       => ($desc ?: ""),
                "logged_millitime"  => millitime()
            ]
        );
    }
    static function is() {

    }
}