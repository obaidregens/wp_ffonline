<?php
class updates {
    function __construct($args) {
        $count = ($args['count'] ?? 3) === -1 ? pow(9,10) : intval($args['count'] ?? 3);
        if (! isset($args['author'])) {
            return false;
        }
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT * FROM wp_posts
            WHERE post_type = 'post'
            AND post_author = %s
            AND post_status = 'publish'
            ORDER BY post_modified DESC
            ",
            [$args['author']]
        );
        $r = $wpdb->get_results($sql);
        $this->total = count($r);
        $stickies = get_option( 'sticky_posts' );
        $pinned_updates = [];
        $other_updates = [];
        foreach ($r as $update ) {
            if (in_array($update->ID,$stickies)) {
                $pinned_updates[] = $update;
                continue;
            }
            $other_updates[] = $update;
        }
        $updates = array_merge($pinned_updates,$other_updates);
        array_splice($updates,$count);
        $this->updates = $updates;
    }
}