<?php
class story {
    public $ID;
    public $post_author;
    public $post_date;
    public $post_date_gmt;
    public $post_content;
    public $post_title;
    public $post_excerpt;
    public $post_status;
    public $comment_status;
    public $ping_status;
    public $post_password;
    public $post_name;
    public $to_ping;
    public $pinged;
    public $post_modified;
    public $post_modified_gmt;
    public $post_content_filtered;
    public $post_parent;
    public $guid;
    public $menu_order;
    public $post_type;
    public $post_mime_type;
    public $comment_count;

    function __construct ($obj) {
        $aa = get_object_vars($obj);
        foreach ($aa as $k => $v) {
            $this->{$k} = $v;
        }
    }
    static function get($id,$current_user = true,$published = true) {
        $c = cache::now();
        if (is_numeric($id)) {
            $story = $c->get(self::key($id));
            if ($story === null) {
                global $wpdb;
                $r = $wpdb->get_results($wpdb->prepare("SELECT * FROM wp_posts WHERE ID = %d LIMIT 1",$id));
                $story = empty($r) ? false : new story($r[0]);
                $c->set(self::key($id),$story);
            }
        } else if ($id instanceof story) {
            $story = $id;
            $c->set(self::key($story->ID),$story);
        } else if (
            is_object($id) &&
            isset($id->ID) &&
            isset($id->post_type) &&
            isset($id->post_status) && 
            isset($id->post_title)
        ){
            $story = new story($id);
            $c->set(self::key($story->ID),$story);
        } else {
            return false;
        }
        if (!$story || $story->post_type !== "book") {
            return false;
        }
        if ($published && $story->post_status !== "publish") {
            return false;
        }
        if ($current_user && !is_current_user($story->post_author) ) {
            return false;
        }
        $author = user::get_by("ID",$story->post_author);
        if (!$author) {
            return false;
        }
        return $story;
    }
    static function uncache($id) {
        cache::now()->delete(self::key($id));
    }
    static function key($id) {
        return "story_$id";
    }
}