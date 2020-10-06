<?php
class offline_stats {
    public static $table = "offline_stats";
    static function new ($args) {
        $args = array_replace([
            'type'          => '',
            'story_id'      => 0,
            'chapter_id'    => 0,
            'landing_id'    => 0,
            'chapter_num'   => 0,
            'stat_millitime'     => 0,
        ],$args);
        $e = new err;
        $e->one_of('type',$args['type'],['offline','story_view','chapter_read']);
        $e->numeric('story_id',$args['story_id']);
        $e->numeric('landing_id',$args['landing_id']);
        $e->numeric('chapter_id',$args['chapter_id']);
        $e->numeric('stat_millitime',$args['stat_millitime']);
        $args['added_millitime'] = millitime();
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        return intval($wpdb->insert_id);
    }
}