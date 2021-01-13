<?php
class feed {
    static function set_settings ($args) {
        if (!is_user_logged_in()) {
            return false;
        }
        $fields = ["fandom","rating","language"];
        foreach ($fields as $field) {
            if (!isset($args[$field])){
                continue;
            }
            $val = $args[$field] ?? null;
            user_settings::set("feed_$field", $val );
        }
    }
    static function get_settings () {
        if (!is_user_logged_in()) {
            return false;
        }
        $settings = [];
        $fields = ["fandom","rating","language"];
        foreach ($fields as $field) {
            $f = user_settings::get("feed_$field" );
            if ($f) {
                $settings[$field] = $f;
            }
        }
        return $settings;
    }
    static function get ($feed_type,$page = 1) {
        $feed_type = $feed_type === "feed" ? "feed" : "reading";
        if ($feed_type === "feed") {
            return self::query($page);
        }
        return self::reading($page);
    }
    protected static function reading($page) {
        $ids = track_reading::get_all_current();

        $a = new book_query;
        $a->query_from_ids($ids,$page);
        return $a;
    }
    protected static function query($page) {
        $prepared = [
            'sort',
            'top/DESC'
        ];
        foreach (["fandom","rating","language"] as $field ) {
            $val = user_settings::get("feed_$field");
            if (!$val) {
                continue;
            }
            foreach ($val as $id) {
                $prepared[] = $field;
                $prepared[] = $id;
            }
        }
        if (count($prepared) <= 2) {
            return false;
        }

        $fill = implode(',',array_fill(0,count($prepared)/2,'(%s,%s)'));
        $query =
        "SELECT * FROM search_cache
        WHERE (`_key`, `_value`) IN ($fill);";

        global $wpdb;
        $full_query = $wpdb->prepare($query,$prepared);
        $results = $wpdb->get_results($full_query);
        
        $sorter = [];
        $seperated = [];
        foreach ($results as $value) {
            if ($value->_key."=".$value->_value === $prepared[0]."=".$prepared[1]) {
                $sorter = json_or_serialize_decode($value->ids);
                continue;
            }
            $k = &$seperated[$value->_key];
            $k = $k ?? [];
            $k = array_merge($k,json_or_serialize_decode($value->ids));
        }
        $results = null;
        $ids = $sorter;
        $sorter = null;
        foreach ($seperated as $_ids) {
            $ids = a_intersect($ids,$_ids);
        }
        
        $a = new book_query;
        $a->query_from_ids($ids,$page);
        return $a;
    }
}