<?php
class track_reading {
    public static $table = "track_reading";
    protected static $cache = [];
    static function record($chapter_id,$para) {
        if (intval($para) < 1) {
            return false;
        }
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
        if (intval($para) < 1) {
            return false;
        }
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
    static function finish($story) {
        $story = story::get( $story, false );
        if (!$story) {
            return false;
        }
        $sql =
        "SELECT
            wp_posts.ID as chapter_id,
            wp_postmeta.meta_value as chapter_num FROM wp_posts
        INNER JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID
        WHERE wp_posts.post_type = 'chapter'
        AND wp_posts.post_status = 'publish'
        AND wp_posts.post_parent = %d
        AND wp_postmeta.meta_key = 'chapter_order'
        ORDER BY CAST(wp_postmeta.meta_value as unsigned) DESC
        LIMIT 1";
        
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[$story->ID]));

        if (empty($r)) {
            return false;
        }

        $wpdb->insert(
            self::$table,
            [
                'user_id'       => get_current_user_id(),
                'story_id'      => $story->ID,
                'chapter_id'    => $r[0]->chapter_id,
                'chapter_num'   => $r[0]->chapter_num,
                'para'          => 0,
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
                $table.`chapter_num`,
                $table.`para`
            FROM $table
            WHERE $table.`user_id` = %d
            AND $table.`story_id` IN ($placeholder)
            ORDER BY $table.`ID` ASC",
        array_merge([get_current_user_id()],$story_ids));
        $r = $wpdb->get_results($sql);

        $ee = [];
        foreach ($r as $row) {
            $ee[$row->story_id] = [
                'chapter'   => intval($row->chapter_id),
                'para'      => intval($row->para),
                'num'       => intval($row->chapter_num)
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
    static function get_all_current() {
        if (!is_user_logged_in()) {
            return false;
        }
        global $wpdb;

        $table = self::$table;
        $sql = $wpdb->prepare(
            "SELECT
                $table.`story_id`,
                $table.`chapter_id`,
                $table.`chapter_num`,
                $table.`para`
            FROM $table
            WHERE $table.`user_id` = %d
            ORDER BY $table.`ID` ASC",
        [get_current_user_id()]);
        $r = $wpdb->get_results($sql);

        $ee = [];
        foreach ($r as $row) {
            $ee[$row->story_id] = [
                'chapter'   => intval($row->chapter_id),
                'para'      => intval($row->para),
                'num'       => intval($row->chapter_num)
            ];
        }
        self::$cache = array_replace(self::$cache,$ee);

        // Now Extract Currently Reading
        $story_ids = array_keys($ee);
        if (empty($story_ids)) {
            return [];
        }

        // Find Chapter Count of Each
        $placeholder = sqlPlaceholder($story_ids);
        $sql = (
            "SELECT post_parent,COUNT(post_parent) AS c FROM wp_posts
            WHERE post_parent IN ($placeholder)
            AND post_type = 'chapter'
            AND post_status = 'publish'
            GROUP BY post_parent"
        );
        $sql = $wpdb->prepare($sql,$story_ids);
        $story_chapters = array_column($wpdb->get_results($sql),'c','post_parent');
        
        $story_ids = [];
        foreach ($story_chapters as $story_id => $chapters) {
            $num = $ee[$story_id]['num'];
            $para = $ee[$story_id]['para'];
            if (!($num === intval($chapters) && $para === 0)) {
                $story_ids[] = $story_id;
            }
        }

        return $story_ids;
    }
}