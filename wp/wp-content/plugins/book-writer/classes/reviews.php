<?php
class reviews {
    static function new($args) {
        if (
            ! isset($args['chapter_id']) ||
            ! isset($args['review']) ||
            trim($args['review']) === ''
        ){
            return false;
        }
        $chapter = get_post( $args['chapter_id'] );
        if (! $chapter || $chapter->post_type !== 'chapter' || !self::can_review($args['chapter_id']) ){
            return false;
        }
        if (isset($args['reply_to'])) {
            $comment = get_comment( $args['reply_to'] );
            if (
                ! $comment ||
                ! is_current_user($chapter->post_author) ||
                is_current_user($comment->user_id)
            ) {
                return false;
            }
        }
        $comment_id = wp_insert_comment(array(
            'user_id'           => get_current_user_id(),
            'comment_post_ID'   => $args['chapter_id'],
			'comment_parent'	=> $args['reply_to'] ?? 0,
            'comment_content'   => $args['review'],
            'comment_author_IP' => $_SERVER['REMOTE_ADDR']
        ));
        $inst = new notifications_insert;
        $inst->addReview($comment_id);
        return $comment_id;
    }
    static function query($args = []) {
        function build_comment($comment,$chapter_author,$current_user_id) {
            $comment_author = intval($comment->user_id);
            $user = get_userdata( $comment->user_id );
            $Suser = [
                'ID'        => $comment_author,
                'name'      => $comment_author === 0 ? 'Anonymous' : '@' . $user->user_login,
                'checked'   => true
            ];
            $Scomment = [
                'ID'        => intval($comment->comment_ID),
                'content'   => $comment->comment_content,
                'time'      => human_time_diff( strtotime( $comment->comment_date ) ),
                'chapter'   => [
                    'num'       => $chapter_num ?? 0,
                    'title'     => $chapter->post_title ?? '',
                ],
                'user'      => [
                    'self'          => $chapter_author === $comment_author,
                    'ID'            => $comment_author,
                    'name'          => $comment_author === 0 ? 'Anonymous' : '@' . $user->user_login,
                    'can_delete'    => reviews::can_delete($comment),
                    'can_reply'     => $current_user_id === $chapter_author && $comment_author !== $current_user_id && $current_user_id !== 0
                ]
            ];
            return [
                'user'      => $Suser,
                'comment'   => $Scomment
            ];
        }
        $args = array_replace([
            'exclude_users' => [],
            'orderby'       => 'comment_date',
            'order'         => 'DESC',
            'per_page'      => 10,
            'page'          => 1,
            'chapter'       => 0,
            // 'book'          => ''
        ],$args);

        $comment_query = (new WP_Comment_Query([
            'author__not_in'    => $args['exclude_users'],
            'number'            => $args['per_page'],
            'paged'             => $args['page'],
            'orderby'           => $args['orderby'],
            'order'             => $args['order'],
            'post_id'           => $args['chapter'],
            'parent'            => 0,
            // 'post_parent'       => $args['book'],
            'hierarchical'	    => 'threaded'
        ]))->comments;
        $chapter = get_post($args['chapter']);
        $chapter_author = intval($chapter->post_author);
        $chapter_num = intval(get_post_meta( $chapter->ID, 'chapter_order', true ));
        $comments = [];
        $current_user_id = intval(get_current_user_id());
        $users = [];
        foreach ($comment_query as $k => $comment) {
            $f = build_comment($comment,$chapter_author,$current_user_id);
            $comment_author = intval($comment->user_id);
            $users[$comment_author] = $f['user'];
            $children = $comment->get_children();
            $f['comment']['replies'] = [];
            foreach ($children as $child) {
                $f['comment']['replies'][] = build_comment($child,$chapter_author,$current_user_id)['comment'];
            }
            $comments[] = $f['comment'];
        }
        foreach ($args['exclude_users'] as $user_id) {
            $user_id = intval($user_id);
            if (isset($users[$user_id])) {
                continue;
            }
            $user = get_userdata( $user_id );
            $users[$user_id] = [
                'ID'        => $user_id,
                'name'      => $user_id === 0 ? 'Anonymous' : '@' . $user->user_login,
                'checked'   => false
            ];
        }
        return [
            'reviews'   => $comments,
            'users'     => array_values($users)
        ];
    }
    static function delete($comment_id) {
        if ( ! self::can_delete($comment_id) ){
            return false;
        }
        wp_delete_comment($comment_id);
    }
    static function can_delete($comment_id) {
        if ($comment_id instanceof WP_Comment) {
            $comment = $comment_id;
        }
        else {
            $comment = get_comment($comment_id);
        }
        if ( !is_user_logged_in() || !$comment ){
            return false;
        }
        if ( is_current_user($comment->user_id) ) {
            return true;
        }
        $chapter = get_post($comment->comment_post_ID);
        if (! $chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
            return false;
        }
        if ( intval($comment->user_id) === 0 && is_current_user($chapter->post_author) ) {
            return true;
        }
        return false;
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
        $book_id = $chapter_or_book->post_type === 'book' ? $chapter_id_or_book->ID : $chapter_or_book->post_parent;
        return
            comments_open( $book_id ) &&
            (get_post_meta( $book_id, 'anon_review', true) === 'true' || $logged_in );
    }
}