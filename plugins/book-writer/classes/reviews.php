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
        if (! $chapter || $chapter->post_type !== 'chapter' || self::status($args['chapter_id']) === false){
            return false;
        }
        if (isset($args['reply_to'])) {
            $comment = get_comment( $args['reply_to'] );
            if (
                ! $comment ||
                intval($comment->user_id) === get_current_user_id() ||
                intval($chapter->post_author) !== get_current_user_id()
            ) {
                return false;
            }
        }
        $comment_id = wp_insert_comment(array(
            'user_id'           => get_current_user_id(),
            'comment_post_ID'   => $args['chapter_id'],
			'comment_parent'	=> $args['reply_to'] ?? 0,
            'comment_content'   => htmlspecialchars($args['review']),
            'comment_author_IP' => $_SERVER['REMOTE_ADDR']
        ));
        return $comment_id;
    }
    static function get($chapter_id) {
        return (new WP_Comment_Query(array(
            'post_id' 		=> $chapter_id,
            'hierarchical'	=> 'threaded',
        )))->comments;
    }
    static function delete($comment_id) {
        $comment = get_comment($comment_id);
        if (! $comment || get_current_user_id() !== intval($comment->user_id) ){
            return false;
        }
        wp_delete_comment($comment->comment_ID);
    }
    static function status($chapter_id){
        $chapter = get_post($chapter_id);
        return $chapter &&
        $chapter->post_type === 'chapter' &&
        comments_open( $chapter_id ) &&
        (get_post_meta($chapter->post_parent, 'anon_review', true) === 'true' || is_user_logged_in() );
    }
}