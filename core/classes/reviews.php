<?php
class reviews {
    public static $table = 'reviews';
    protected static $types = ['chapter'];
    protected static $status = ['public'];

    static function new($args) {
        $args = array_replace([
            'review'        => "",
            'user_id'       => get_current_user_id(),
            'reply'         => 0,
            'quote'         => "",
            'status'        => 'public',
            'millitime'     => millitime()
        ],$args);
        $e = new err;
        $e->is_required([
            'type',
            'type_id',
            'landing_id'
        ],$args);
        $e->one_of('type',$args['type'],self::$types);
        $e->one_of('status',$args['status'],self::$status);
        $e->numeric('type_id',$args['type_id']);
        $e->numeric('landing_id',$args['landing_id']);
        $e->numeric('user_id',$args['user_id']);
        if ( strlen(trim($args['review'])) < 3 ) {
            $e->add("review",'Review has to be of at least 3 characters.');
        }
        if (intval($args['reply']) !== 0 ) {
            $reply = reviews::get($args['reply']);
            $chapter = get_post( $args['type_id'] );
            $can_reply = reviews::can_reply($reply->user_id,$chapter);
            if (!$can_reply) {
                $e->add('reply',"Can't reply to review.");
            }
        }
        if ($e->has()){
            return $e;
        }
        global $wpdb;
        $wpdb->insert(
            self::$table,
            $args
        );
        $review_id = (int) $wpdb->insert_id;
        $inst = new notifications_insert;
        $inst->addReview($review_id);
        return $review_id;
    }
    static function get($id) {
        if ( is_object($id) && isset($id->ID) && isset($id->review) && isset($id->reply) && isset($id->millitime) ) {
            return $id;
        }
        global $wpdb;
        $table = self::$table;
        $r = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE ID = %d",[$id]));
        return empty($r) ? false : $r[0];
    }
    static function delete($review_id) {
        if ( !self::can_delete($review_id) ){
            return false;
        }
        global $wpdb;
        $wpdb->update(self::$table,[
            'status'    => "trash"
        ],[
            "ID"        => $review_id
        ]);
    }
    protected static function build_comment($comment,$author,$chapter,$current_user_id) {
        $comment_author = intval($author->ID);
        $chapter_author = intval($chapter->post_author);
        $current_user_id = intval($current_user_id);
        
        $Suser = [
            'ID'        => $comment_author,
            'name'      => $comment_author === 0 ? 'Anonymous' : '@' . $author->user_login,
            'checked'   => true
        ];
        $Scomment = [
            'ID'        => intval($comment->ID),
            'content'   => $comment->review,
            'quote'     => $comment->quote,
            'time'      => human_time_diff( intval( $comment->millitime )/1000 ),
            // 'chapter'   => [
            //     'num'       => $chapter_num ?? 0,
            //     'title'     => $chapter->post_title ?? '',
            // ],
            'user'      => [
                'self'          => $chapter_author === $comment_author,
                'ID'            => $comment_author,
                'name'          => $comment_author === 0 ? 'Anonymous' : '@' . $author->user_login,
                'can_delete'    => reviews::can_delete($comment),
                'can_reply'     => reviews::can_reply($comment_author,$chapter)
            ]
        ];
        return [
            'user'      => $Suser,
            'comment'   => $Scomment
        ];
    }
    static function query(array $args = []) {
        $args = array_replace([
            'exclude_users' => [],
            'orderby'       => 'millitime',
            'order'         => 'DESC',
            'per_page'      => 10,
            'page'          => 1,
            'chapter'       => 0,
            'story'         => 0,
            'threaded'      => true,
            'with_users'    => false,
            'status'        => ['public']
        ],$args);
        if (empty($args['status'])) {
            return [];
        }
        $table = self::$table;
        $fields = [
            "$table.`ID` AS ID",
            "$table.`type_id` AS type_id",
            "$table.`review` AS review",
            "$table.`quote` AS quote",
            "$table.`user_id` AS user_id",
            "$table.`reply` AS reply",
            "$table.`status` AS status",
            "$table.`millitime` AS millitime"    
        ];
        $sql =
        "SELECT " . implode(',',$fields) .  " FROM $table
        INNER JOIN wp_posts ON wp_posts.`ID` = $table.`type_id`
        WHERE wp_posts.`post_type` = 'chapter'
        AND $table.`status` IN (" . sqlPlaceholder($args['status']) . ")
        AND reply = 0";
        $prep = $args['status'];
        if (count($args['exclude_users']) > 0) {
            $sql .= " AND $table.`user_id` NOT IN (" . sqlPlaceholder($args['exclude_users'],"%d") . ")";
            $prep = array_merge($prep,$args['exclude_users']);
        }
        if ($args['chapter'] !== 0) {
            $sql .= " AND $table.`type_id` = %d";
            $prep[] = $args['chapter'];
        }
        if ($args['story'] !== 0) {
            $sql .= " AND wp_posts.`post_parent` = %d";
            $prep[] = $args['story'];
        }
        $orderby = [
            'millitime'        => "$table.millitime"
        ][in_array($args['orderby'],["millitime"]) ? $args['orderby'] : "millitime"];
        $args['order'] = strtoupper($args['order']);
        $order = in_array($args['order'],["DESC","ASC"]) ? $args['order'] : "DESC";

        $sql .= " ORDER BY $orderby $order";
        $sql .= " LIMIT " . intval($args['per_page']) . " OFFSET " . (intval($args['page'])-1)*intval($args['per_page']);

        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,$prep));
        
        if ( beta::is() && beta::can() && is_test_story($args['chapter']) ) {
            $exclude_users = array_unique(array_merge($args['exclude_users'],array_column($r,'user_id')));
            arr::remove($exclude_users,get_current_user_id());
            if ($args['exclude_users'] != $exclude_users) {
                return self::query(array_replace($args,['exclude_users' => $exclude_users]));
            }
        }

        $args['threaded'] = (bool) $args['threaded'];
        if (!$args['threaded']) {
            return $r;
        }
        $replies = empty($r) ? [] : $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE reply IN (" . sqlPlaceholder($r) . ")",array_column($r,'ID')));
        $r = array_merge($r,$replies);
        $replies = null;

        $a = [];
        foreach ($r as $v) {
            $k = &$a[$v->ID];
            if (isset($k->review)) {continue;}
            if (intval($v->reply) === 0) {
                $replies = $k->replies ?? [];
                $k = $v;
                $k->replies = $replies;
                continue;
            }
            unset($a[$v->ID]);
            $a[$v->reply] = $a[$v->reply] ?? ((object) [
                'replies'   => []
            ]);
            $a[$v->reply]->replies[] = $v;
        }
        return [
            'reviews'       => $a,
            'users'         => array_unique(array_column($r,'user_id'))
        ];
    }
    static function queryBuild(array $args = []) {
        $args['threaded'] = true;
        $args['with_users'] = true;
        $r = self::query($args);

        global $wpdb;
        $user_ids_in = array_merge($r['users'],$args['exclude_users'] ?? []);
        $authors = empty($user_ids_in) ? [] : (
            $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM wp_users
                    WHERE user_status = 0
                    AND `ID` IN (" . sqlPlaceholder($user_ids_in,"%d") . ")",
                    $user_ids_in
                ),
            OBJECT_K)
        );
        $r = $r['reviews'];

        $current_user_id = intval(get_current_user_id());

        $reviews = [];
        $users = [];
        foreach ($r as $review) {
            $chapter = get_post($review->type_id);
            $chapter_author = intval($chapter->post_author);
            
            $f = self::build_comment($review,$authors[$review->user_id],$chapter,$current_user_id);
            $comment_author = intval($review->user_id);
            $users[$comment_author] = $f['user'];
            $children = $review->replies;
            $f['comment']['replies'] = [];
            foreach ($children as $child) {
                $f['comment']['replies'][] =
                self::build_comment($child,$authors[$child->user_id],$chapter,$current_user_id)['comment'];
            }
            $reviews[] = $f['comment'];
        }
        foreach ($args['exclude_users'] as $user_id) {
            $user_id = intval($user_id);
            if (isset($users[$user_id])) {
                continue;
            }
            $user = $authors[strval($user_id)];
            $users[$user_id] = [
                'ID'        => $user_id,
                'name'      => $user_id === 0 ? 'Anonymous' : '@' . $user->user_login,
                'checked'   => false
            ];
        }
        return [
            'reviews'   => $reviews,
            'users'     => array_values($users)
        ];
    }
    // Helpers
    static function can_delete($review_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        $review = reviews::get($review_id);
        if (!$review) {
            return false;
        }
        if ( is_current_user($review->user_id) ) {
            return true;
        }
        $chapter = get_post($review->type_id);
        if (! $chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
            return false;
        }
        if ( intval($review->user_id) === 0 && is_current_user($chapter->post_author) ) {
            return true;
        }
        return false;
    }
    static function can_reply($comment_author,$chapter) {
        $chapter_author = intval($chapter->post_author);
        $current_user_id = intval(get_current_user_id());
        if ($current_user_id === 0) {
            return false;
        }
        if ($comment_author === $current_user_id) {
            return false;
        }
        if ($current_user_id === $chapter_author) {
            return true;
        }
        return beta::is() && beta::can() && is_test_story($chapter);
    }
    static function can_review($chapter_id_or_book_id,$logged_in = null){
        if ($logged_in === null) {
            $logged_in = is_user_logged_in();
        }
        $chapter_or_book = get_post($chapter_id_or_book_id);
        if (! $chapter_or_book) {
            return false;
        }
        if (! in_array($chapter_or_book->post_type,['chapter','book'])){
            return false;
        }
        if (is_test_story($chapter_or_book) && beta::is() && beta::can()) {
            return true;
        }
        $book_id = $chapter_or_book->post_type === 'book' ? $chapter_or_book->ID : $chapter_or_book->post_parent;
        $book = $chapter_or_book->post_type === 'book' ? story::get($chapter_or_book,false) : story::get($book_id,false);
        return
            $book &&
            comments_open( $book_id ) &&
            (get_post_meta( $book_id, 'anon_review', true) === 'true' || $logged_in );
    }
    static function count($chapter_id_or_book_id) {
        $chapter_or_book = get_post($chapter_id_or_book_id);
        if (! $chapter_or_book) {
            return 0;
        }
        $table = self::$table;
        if ($chapter_or_book->post_type === 'book') {
            $sql =
            "SELECT COUNT($table.ID) AS c FROM $table
            INNER JOIN wp_posts ON wp_posts.ID = $table.ID
            WHERE wp_posts.post_parent = %d
            GROUP BY wp_posts.post_parent";
        } else if ($chapter_or_book->post_type === 'chapter') {
            $sql = "SELECT COUNT(ID) AS c FROM $table
            WHERE type_id = %d";
        } else {
            return 0;
        }
        global $wpdb;
        $r = $wpdb->get_results($wpdb->prepare($sql,[$chapter_or_book->ID]));
        return empty($r) ? 0 : intval($r[0]->c);
    }
}