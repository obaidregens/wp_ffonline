<?php
class book_stats extends stats {
    function __construct($story_id) {
        $this->updated = time();

        // Tables
        $landings_table = stats::$landing_table;
        $actions_table = stats::$actions_table;

        // Consts
        $this_week_start = strtotime('-1 week monday 00:00:00');
        $min30 = 60*30;

        // Sql
        $sql =
        "SELECT wp_posts.ID as ID,wp_postmeta.meta_value as num,$landings_table.vfs,$actions_table.timestamp FROM $landings_table
        INNER JOIN $actions_table ON $landings_table.ID = $actions_table.landing_id
        INNER JOIN wp_posts ON wp_posts.ID = $landings_table.type_id
        INNER JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID
        WHERE wp_postmeta.meta_key = 'chapter_order'
        AND wp_posts.post_parent = %d
        AND $landings_table.type = 'chapter'
        ORDER BY $actions_table.timestamp ASC";
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[$story_id]));

        // Loop
        $this->chapters = [];
        $thisweek_sessions = 0;
        $alltime_sessions = 0;
        $last_actions = [];
        foreach ($r as $action ) {
            // Prep
            $prev = &$last_actions['story'][$action->vfs];
            $prev_chap = &$last_actions[$action->ID][$action->vfs];
            $action->timestamp = (int) $action->timestamp;
            // Logic
            // Story Logic
            if ( ($action->timestamp - ($prev->timestamp ?? 0)) > $min30 ) {
                $alltime_sessions += 1;
                if ($action->timestamp >= $this_week_start){
                    $thisweek_sessions += 1;
                }
            }
            // Chapter Logic
            if ( ($action->timestamp - ($prev_chap->timestamp ?? 0)) > $min30 ) {
                $chap = &$this->chapters[$action->ID];
                $chap->alltime = ($chap->alltime ?? 0) + 1;
                if ($action->timestamp >= $this_week_start){
                    $chap->thisweek = ($chap->thisweek ?? 0) + 1;
                }
            }
            // Finalize
            $prev = $action;
            $prev_chap = $action;
        }
        $this->story->thisweek = $thisweek_sessions;
        $this->story->alltime = $alltime_sessions;
    }
    static function cached($story_id) {
        $cache_time = 60*60*6; // 6 Hours

        $fname = MAIN_DIR . "/reports/story-" . $story_id;
        $mtime = filemtime($fname);
        if (file_exists($fname) && (time() - $mtime) <= $cache_time ) {
            $inst = unserialize(file_get_contents($fname));
            if (!isset($inst->updated)) {
                $inst->updated = $mtime;
            }
            return $inst;
        }

        $inst = new book_stats($story_id);

        $dir = dirname($fname);
        if (!is_dir($dir)) {
            mkdir($dir,0777,true);
        }
        file_put_contents($fname,serialize($inst));

        return $inst;
    }
}