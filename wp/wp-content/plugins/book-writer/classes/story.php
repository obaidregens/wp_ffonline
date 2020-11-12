<?php
class story {
    static function get($id,$current_user = true,$published = true) {
        $story = get_post($id);
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
}