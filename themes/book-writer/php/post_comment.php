<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        echo '8';exit();
    }
    global $withcomments;
    $withcomments = 1;
    if (! comments_open()){
        echo '2';
        exit();
    }
    if (! is_user_logged_in() && get_post_meta($book->ID,'anon_review',true) != 'true'){
        echo 3;
        exit();
    }
    if ($_POST['action'] == 'insert'){
        $comment_id = wp_insert_comment(array(
            'user_id'           => get_current_user_id(),
            'comment_post_ID'   => $_POST['id'],
            'comment_content'   => htmlspecialchars($_POST['comment']),
            'comment_author_IP' => $_SERVER['REMOTE_ADDR']
        ));  
		$post = get_post($_POST['id']);
		add_notification('comment',$comment_id);
    }
    else if ($_POST['action'] == 'delete'){
        if (! get_current_user_id() == get_comment($_POST['id'])->user_id){
            echo 2;
            exit();
        }
		$post = get_post(get_comment($_POST['id'])->comment_post_ID);
        wp_delete_comment($_POST['id']);
    }
    else if ($_POST['action'] == 'reply'){
		$comment = get_comment($_POST['id']);
		if ($comment == null){
			echo 2;
			exit();
		}
		$post = get_post($comment->comment_post_ID);
        if ($post->post_author != get_current_user_id()){
			echo 4;
			exit();
		}
		$comment_id = wp_insert_comment(array(
            'user_id'           => get_current_user_id(),
            'comment_post_ID'   => $post->ID,
			'comment_parent'	=> $_POST['id'],
            'comment_content'   => htmlspecialchars($_POST['comment']),
            'comment_author_IP' => $_SERVER['REMOTE_ADDR']
        ));
        add_notification('comment',$comment_id);
    }
    get_template_part('comments');
 exit;
}