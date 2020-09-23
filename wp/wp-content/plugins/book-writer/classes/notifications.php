<?php
class notifications {
    protected static $table = 'notifications';
    static function lastOpen() {
        global $wpdb;
        $lastOpen = $wpdb->get_results("SELECT timestamp FROM stats_actions ORDER BY timestamp DESC LIMIT 1");
        return empty($lastOpen) ? 0 : intval($lastOpen[0]->timestamp);
    }
    static function createNotification($row) {
        switch ($row->notification_type) {
            case 'chapter_review':
                return [
                    'message'   => "Someone left a review for you.",
                    'link'      => rtrim(get_permalink( $row->type_by_id ),'/') . '/#review-' . $row->type_of_id
                ];
            case 'review_reply':
                return [
                    'message'   => "The author replied to your review.",
                    'link'      => rtrim(get_permalink( get_comment($row->type_by_id)->comment_post_ID ),'/') . '/#review-' . $row->type_of_id
                ];
            case 'chapter_vote':
                return [
                    'message'   => "Your chapter got another vote!",
                    'link'      => get_permalink( $row->type_of_id )
                ];
            case 'user_update':
                return [
                    'message'   => "An author you follow just posted an update!",
                    'link'      => rtrim(get_author_posts_url( $row->type_by_id ),'/') 
                ];
            case 'follow_user':
                return [
                    'message'   => "Someone followed you.",
                    'link'      => rtrim(get_author_posts_url( get_current_user_id() ),'/')
                ];
            case 'follow_collection':
                return [
                    'message'   => "Someone followed your collection.",
                    'link'      => rtrim(collection_helpers::link( $row->type_of_id ),'/')
                ];
            default:
                return false;
        }
    }
    static function get(){
        $lastOpen = self::lastOpen();
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM notifications WHERE user_id = %d ORDER BY timestamp ASC",[get_current_user_id()]);
        $r = $wpdb->get_results($sql);
        $rr = [];
        foreach ($r as $row ) {
            $n = self::createNotification($row);
            $n['read'] = intval($row->timestamp) < $lastOpen;
            $rr[] = $n;
        }
        return $rr;
    }
}
class notifications_insert extends notifications {
    function updateStory($chapter_id) {
        $chapter = get_post($chapter_id);
        if (! $chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
            return false;
        }
        $followers = follow::query_by('user','type_id',$chapter->post_author);
        $chapter_author = intval($chapter->post_author);
        foreach ( $followers as $follower ) {
            if ( $chapter_author === intval($follower->user_id) ) {
                continue;
            }
            $args['user_id'] = $follower->user_id;
            self::insert($args);
        }
        $sql = $wpdb->prepare("
        SELECT follows.user_id as user_id FROM collection_books
        INNER JOIN follows.type_id = collection_books.collection_id
        WHERE collection_books.book_id = %d
        AND follows.type = 'collection'
        AND follows.notifications = 'all'
        ",[$chapter->post_parent]);
        $followers = $wpdb->get_results($sql);
        foreach ( $followers as $follower ) {
            if ( $chapter_author === intval($follower->user_id) ) {
                continue;
            }
            $args['user_id'] = $follower->user_id;
            self::insert($args);
        }
    }
    function addReview($review_id) {
        $review = get_comment( $review_id );
        $chapter = get_post( $review->comment_post_ID );
        if ( !$review || !$chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
            return false;
        }
        if ( 
            intval($chapter->post_author) === intval($review->user_id) &&
            intval($review->comment_parent) === 0
        ) {
            return false;
        }
        $parent_0 = intval($review->comment_parent) === 0;
        $args = [
            'notification_type' => $parent_0 ? 'chapter_review' : 'review_reply',
            'type_of'           => 'review',
            'type_of_id'        => $review,
            'type_by'           => $parent_0 ? 'chapter' : 'review',
            'type_by_id'        => $parent_0 ? $chapter->ID : $review->comment_parent,
            'user_id'           => $parent_0 ? $chapter->post_author : get_comment( $review->comment_parent )->user_id
        ];
        self::insert($args);
    }
    function vote($chapter_id,$user_id) {
        $chapter = get_post($chapter_id);
        if (! $chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
            return false;
        }
        if (intval($chapter->post_author) === intval($user_id)) {
            return false;
        }
        $args = [
            'notification_type' => 'chapter_vote',
            'type_of'           => 'chapter',
            'type_of_id'        => $chapter_id,
            'type_by'           => 'user',
            'type_by_id'        => $user_id,
            'user_id'           => $chapter->post_author
        ];
        self::insert($args);
    }
    function addUpdate($update_id) {

        $update = get_post($update_id);
        if (! $update || $update->post_type !== 'post' || $update->post_status !== 'publish') {
            return false;
        }
        $args = [
            'notification_type' => 'user_update',
            'type_of'           => 'update',
            'type_of_id'        => $update->ID,
            'type_by'           => 'user',
            'type_by_id'        => $update->post_author,
        ];
        $followers = follow::query_by('user','type_id',$update->post_author);
        $update_author = intval($update->post_author);
        foreach ($followers as $follower ) {
            if ($update_author === intval($follower->user_id)) {
                continue;
            }
            $args['user_id'] = $follower->user_id;
            self::insert($args);
        }
    }
    function followUser($user_id,$by_user_id) {
        $user = get_userdata($user_id);
        $by_user = get_userdata( $by_user_id );
        if (! $user || !$by_user ) {
            return false;
        }
        if (intval($user->ID) === intval($by_user->ID)) {
            return false;
        }
        $args = [
            'notification_type' => 'follow_user',
            'type_of'           => 'user',
            'type_of_id'        => $user->ID,
            'type_by'           => 'user',
            'type_by_id'        => $by_user->ID,
            'user_id'           => $user->ID
        ];
        self::insert($args);
    }
    function followCollection($collection_id,$by_user) {
        $collection = collection::get_by('ID',$collection_id);
        $by_user = get_userdata( $by_user );
        if (! $collection || !$by_user ) {
            return false;
        }
        if ( intval($collection->author) === intval($by_user->ID) ) {
            return false;
        }
        $args = [
            'notification_type' => 'follow_collection',
            'type_of'           => 'collection',
            'type_of_id'        => $collection->ID,
            'type_by'           => 'user',
            'type_by_id'        => $by_user->ID,
            'user_id'           => $collection->author
        ];
        self::insert($args);
    }
    static function insert($args) {
        $args['timestamp'] = microtime(true);
        $args['email_status'] = 'none';
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        return $wpdb->insert_id;
    }
}