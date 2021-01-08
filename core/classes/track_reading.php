<?php
class track_reading {
    public static $table = "track_reading";
    protected static $cache = [];
    static function record($chapter_id,$para) {
        $chapter = get_post( $chapter_id );
        if (!$chapter || $chapter->post_type !== "chapter") {
            return false;
        }
        $story = story::get( $chapter->post_parent, false );
        if (!$story) {
            return false;
        }
        global $wpdb;
        self::remove_cache($story->ID);
        $wpdb->insert(
            self::$table,
            [
                'user_id'       => get_current_user_id(),
                'story_id'      => $story->ID,
                'chapter_id'    => $chapter->ID,
                'chapter_num'   => get_post_meta( $chapter->ID, 'chapter_order', true ),
                'para'          => intval($para),
                'landing_id'    => landing_id(),
                'millitime'     => millitime()
            ]
        );
        return intval($wpdb->insert_id);
    }
    static function record_by_num($story,$num,$para) {
        global $wpdb;

        $rsql =
        "SELECT chapter.ID as chapter_id FROM wp_postmeta
        INNER JOIN wp_posts as chapter ON chapter.ID = wp_postmeta.post_id
        INNER JOIN wp_posts as book ON book.ID = chapter.post_parent
        WHERE book.ID = %d
        AND book.post_status = 'publish'
        AND book.post_type = 'book'
        AND chapter.post_type = 'chapter'
        AND wp_postmeta.meta_key = 'chapter_order'
        AND wp_postmeta.meta_value = %s";
        
        $r = $wpdb->get_results($wpdb->prepare($rsql,[$story,$num]));
        if (empty($r)) {
            return false;
        }

        self::remove_cache($story);
        $wpdb->insert(
            self::$table,
            [
                'user_id'       => get_current_user_id(),
                'story_id'      => $story,
                'chapter_id'    => $r[0]->chapter_id,
                'chapter_num'   => $num,
                'para'          => intval($para),
                'landing_id'    => landing_id(),
                'millitime'     => millitime()
            ]
        );
        return intval($wpdb->insert_id);

        
    }
    protected static function remove_cache($story) {
        unset(self::$cache[$story]);
    }
    static function cache($story_ids) {
        if (!is_user_logged_in()) {
            return false;
        }
        global $wpdb;

        $table = self::$table;
        $placeholder = sqlPlaceholder($story_ids,'%d');
        $sql = $wpdb->prepare(
            "SELECT
                $table.`story_id`,
                $table.`chapter_id`,
                $table.`para`,
                wp_postmeta.`meta_value` as chapter_num
            FROM $table
            INNER JOIN wp_postmeta ON $table.`chapter_id` = wp_postmeta.`post_id`
            WHERE $table.`user_id` = %d
            AND $table.`story_id` IN ($placeholder)
            AND wp_postmeta.meta_key = 'chapter_order'
            ORDER BY $table.`ID` ASC",
        array_merge([get_current_user_id()],$story_ids));
        $r = $wpdb->get_results($sql);

        $ee = [];
        foreach ($r as $row) {
            $ee[$row->story_id] = [
                'chapter'   => $row->chapter_id,
                'para'      => $row->para,
                'num'       => $row->chapter_num
            ];
        }
        foreach (array_diff($story_ids,array_keys($ee)) as $id) {
            $ee[$id] = false;
        }
        self::$cache = array_replace(self::$cache,$ee);
    }
    static function get($story_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        if (!isset(self::$cache[$story_id])) {
            self::cache([$story_id]);
        }
        return self::$cache[$story_id];
    }
}