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
        $rsql = "SELECT import_story FROM $table WHERE import_user = %s AND import_from = %s ";
        $sql = $wpdb->prepare($rsql,[$current,'ffn']);
        $existing = array_column($wpdb->get_results($sql),'import_story');    
        $remove_requests = array_diff($existing,$stories);
        $new_requests = array_diff($stories,$existing);
        foreach ($remove_requests as $storyId ) {
            $wpdb->delete(
                self::$table,
                [
                    'import_story'      => $storyId,
                    'import_from'       => 'ffn',
                    'import_status'     => 'pending'
                ]
            );
        }
        foreach ($new_requests as $storyId ) {
            $wpdb->insert(
                $table,
                [
                    'import_user'   => $current,
                    'import_from'   => 'ffn',
                    'user_id'       => get_current_user_id(),
                    'story_id'      => 0,
                    'import_story'  => $storyId,
                    'import_status' => 'pending',
                    'request_time'  => time(),
                    'import_time'   => 0,
                    'viewed_time'   => 0,
                    'import_favs'   => 0,
                    'import_follows'=> 0
                ]
            );    
        }
    }
    protected static function get_from_ffn($ffn_author) {
        $html = file_get_contents("https://www.fanfiction.net/u/" . $ffn_author);
        if (! $html) {return [];}
        ob_start();
        $gzip = gzdecode($html);
        ob_end_clean();
        if ($gzip !== false){
            $html = $gzip;
            $gzip = null;
        }
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $r = $doc->loadHTML($html);
        $html = null;
        if (!$r){
            return [];
        }
        $XPath = new DOMXPath ($doc);
        $nodes = ($XPath->query('//div[@class="z-list mystories"]/a[@class="stitle"]'));
        $return = [];
        foreach ($nodes as $node ) {
            $title = $node->textContent;
            $href = $node->attributes->getNamedItem("href")->value;
            $id = arr::non_empty(explode('/',$href))[1];
            $return[] = [
                'ID'        => intval($id),
                'title'     => $title
            ];
        }
        return $return;
    }
    static function view_all ($author_id) {
        $r = c_user::get($author_id);
        if ($r === false || !is_current_user($r)) {
            return [];
        }
        $table = self::$table;
        global $wpdb;
        $sql = $wpdb->prepare("SELECT import_story,import_status,story_id FROM $table WHERE import_user = %s",[$author_id]);
        $results = $wpdb->get_results($sql);
        $r = array_column($results,'import_status','import_story');
        $ids = array_column($results,'story_id','import_story');
        $stories = self::get_from_ffn($author_id);
        foreach ($stories as $k => $story ) {
            $s = &$stories[$k];
            $s['status'] = $r[strval($s['ID'])] ?? 'not_imported';
            $s['storyID'] = $ids[strval($s['ID'])] ?? 0;
        }
        return $stories;
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