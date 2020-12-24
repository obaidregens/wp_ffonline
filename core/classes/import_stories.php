<?php
class import_stories {
    protected static $table = 'import_stories';
    static function import_request($stories) {
        $current = c_user::current();
        if ($current === false) {
            return false;
        }
        $table = self::$table;
        global $wpdb;
        $rsql =
        "SELECT import_story FROM $table
        WHERE import_user = %s
        AND import_from = %s
        AND import_status = %s";
        $sql = $wpdb->prepare($rsql,[$current,'ffn','pending']);
        $existing = array_column($wpdb->get_results($sql),'import_story');    
        $remove_requests = array_diff($existing,$stories);
        $new_requests = array_diff($stories,$existing);
        foreach ($remove_requests as $storyId ) {
            $wpdb->update(
                self::$table,
                [
                    'import_status'     => 'not_imported'
                ],
                [
                    'import_story'      => $storyId,
                    'import_from'       => 'ffn',
                    'import_status'     => 'pending'
                ]
            );
        }
        foreach ($new_requests as $storyId ) {
            $wpdb->update(
                $table,
                [
                    'request_time'  => time(),
                    'import_user'   => $current,
                    'user_id'       => get_current_user_id(),
                    'import_status' => 'pending',
                ],
                [
                    'import_status' => 'not_imported',
                    'import_story'  => $storyId,
                ]
            );    
        }
    }
    static function view_all ($author_id) {
        $r = c_user::get($author_id);
        if ($r === false || !is_current_user($r)) {
            return [];
        }
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT
                `import_story` as `ID`,
                `import_status` as `status`,
                `story_id` as `storyID`,
                `import_title` as `title`
            FROM $table
            WHERE import_user = %s",
            [$author_id]
        );
        $results = $wpdb->get_results($sql,ARRAY_A);
        foreach ($results as $k => $story) {
            if (intval($story['storyID']) !== 0 && $story['title'] === "") {
                $s = story::get($story['storyID'],false,false);
                if (!$s) {continue;}
                $results[$k]['title'] = $s->post_title;
            }
        }
        return $results;
    }
    static function get_status ($storyId) {
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $table WHERE story_id = %d",[$storyId]);
        $r = $wpdb->get_results($sql);
        return (empty($r) ? "not_imported" : $r[0]->import_status);
    }
    static function cancel_auto_update($story_id) {
        global $wpdb;
        $wpdb->update(
            self::$table,
            [ "import_status" => "imported" ],
            [
                "story_id"      => $story_id,
                "import_status" => "live"
            ]
        );
    }
    static function set_re_import($story_id,$set) {
        global $wpdb;
        $wpdb->update(
            self::$table,
            [ "import_status" => ($set ? "reimport" : "imported") ],
            [
                "story_id"      => $story_id,
                "import_status" => (!$set ? "reimport" : "imported")
            ]
        );
    }
}