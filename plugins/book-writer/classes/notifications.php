<?php
class notifications {
    public static function get(){

        if (! is_user_logged_in(  )){
            return array(
                'unread'        => 0,
                'notifications' => array()
            );
        }
        $last_opened = stats::last_online(array(
            'user_ids'      => array(get_current_user_id()),
            'stats'         => array('notifications'),
        ));
        $new = 0;
        $all = array();
        //Books
        $collections = collection::query(array(
            'follower_ids'  => array(get_current_user_id())
        ));
        foreach ($collections as $collection) {
            $books = collection::book_query($collection['ID'],array(
                'order'     => 'DESC',
                'orderby'   => 'modified'
            ));
            foreach ($books as $book ) {
                $timestamp = strtotime($book->post_modified);
                $new += $timestamp > $last_opened ? 1 : 0;
                $this_book = array(
                    'ID'        => $book->ID,
                    'title'     => $book->post_title,
                    'timestamp' => $timestamp,
                    'link'      => get_permalink( $book->ID ),
                    'type'      => 'book_updated',
                    'collection'=> $collection['title']
                );
                $all[] = $this_book;
            }
        }
        //Messages
        $received_messages = chats::query(array(
            'users_included'    => array(get_current_user_id()),
            'author__not_in'    => array(get_current_user_id())
        ));
        foreach ($received_messages as $message) {
            $timestamp = strtotime($message->post_modified);
            $new += $timestamp > $last_opened ? 1 : 0;
            $this_message = array(
                'ID'        => $message->ID,
                'from'      => get_the_author_meta('display_name',$message->post_author),
                'timestamp' => $timestamp,
                'link'      => '/dashboard/chat/' . $message->post_author,
                'type'      => 'message_received',
            );
            $all[] = $this_message;
        }
        //Comments
        $comments = (new WP_Comment_Query( array(
            'post_author__in'           => array(get_current_user_id()),
            'author__not_in'            => array(get_current_user_id()),
        ) ))->comments;
        foreach ($comments as $comment ) {
            $chapter = get_post($comment->comment_post_ID);
            $timestamp = strtotime($comment->comment_date);
            $new += $timestamp > $last_opened ? 1 : 0;
            $this_comment = array(
                'ID'            => $comment->comment_ID,
                'timestamp'     => $timestamp,
                'chapter_title' => $chapter->post_title,
                'book_title'    => get_the_title( $chapter->post_parent ),
                'link'          => get_comment_link( $comment->comment_ID ),
                'type'          => 'comment'
            );
            $all[] = $this_comment;
        }
        array_multisort( array_column($all, "timestamp"), SORT_DESC, $all );
        return array(
            'unread'        => $new,
            'notifications' => $all
        );
    }
    ///////////EMAIL/////////////////
    private static $mail_footer =  'You can disable all notifications here: https://fanfiction.online/dashboard/settings';
    public static function new_chapter($chapter_id){
        $error = new err();
        $chapter = get_post($chapter_id);
        $book = get_post($chapter->post_parent);
        $author = get_userdata( $book->post_author );
        if ($chapter === null || $chapter->post_type !== 'chapter'){
            $error->add('chapter_id','Invalid Chapter ID');
            return $error;
        }
        $collections = collection::query(array(
            'book_ids'      => array($book->ID),
        ));
        $followers = array_column(collection::follower_query(array(
            'collection_ids'    => array_column($collections,'ID'),
            'notifications'     => array('all')
        )),'user_id','collection_id');
        foreach ($collections as $key => $collection) {
            $emails = array_column((new WP_User_Query(array(
                'include'       => $followers[$collections['ID']],
                'fields'        => 'email'
            )))->results,'data');
            $subject = $book->post_title . ' by ' . $author->display_name . ' just got a new chapter!';
            $message = $book->post_title . ' by ' . $author->display_name . 'from your collection \'' . $collection['title'] .  '\' just got a new chapter!' . "\r\n\r\n";
            $message .=  'Chapter ' . get_post_meta($chapter->ID,'chapter_order',true) . ': ' . $chapter -> post_title  . "\r\n";
            $message .= get_permalink($chapter->ID) . "\r\n\r\n";
            $message .= 'You can disable notifications for this collection here: ' . collection::link($collection['ID']) . '\r\n';
            $message .= collection::$mail_footer;
            wp_mail($emails,$subject,$message);
        }
    }
    public static function new_message($message_id){
        $error = new err();
        $message = get_post($message_id);
        if ($message === null || $message->post_type != 'message'){
            $error->add('message_id','Message ID invalid.');
            return $error;
        }
        $msg_bw = wp_get_object_terms($message_id,'message_between',array('fields'=>'names'));
        unset($msg_bw[array_search(strval($message->post_author),$msg_bw)]);
        $to = get_userdata( array_values($msg_bw)[0] );

        $author = get_userdata( $message->post_author );
        $subject = $author->display_name . ' just messaged you.';
        $message = $author->display_name . ' just messaged you.' . "\r\n";
        $message .= 'See here: ' . get_permalink($message->ID) . "\r\n\r\n";
        $message .= notifications::$mail_footer;
        wp_mail($to->user_email,$subject,$message);
    }
    public static function new_comment($comment_id){
        $error = new err();
        $comment = get_comment($comment_id);
        $chapter = get_post($comment->comment_post_ID);
        $chapter_author = get_userdata( $chapter->post_author );
        if ($comment === null){
            $error->add('comment_id','Comment ID invalid.');
            return $error;
        }
        $subject = 'New comment on your chapter \'' . $chapter->post_title . '\'.';
        $message = 'You just got a new comment on your chapter \'' . $chapter_on->post_title . '\'.' . "\r\n";
        $message .= 'See here: ' . get_comment_link($id) . "\r\n\r\n";
        $message .= notifications::$mail_footer;
        wp_mail($chapter_author->user_email,$subject,$message);
    }
}