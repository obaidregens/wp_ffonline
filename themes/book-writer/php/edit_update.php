<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['update_id'])){
    if(! is_user_logged_in()){
        echo 3;
    }
    else if (get_post($_POST['update_id']) != null && get_post($_POST['update_id'])->post_author == get_current_user_id() && get_post($_POST['update_id'])->post_type == 'post' && isset($_POST['action']) && ($_POST['action'] == 'delete' || $_POST['action'] == 'stick')){
        if ($_POST['action'] == 'delete'){
            wp_delete_post($_POST['update_id']);
            echo 1;
        }
        else if ($_POST['action'] == 'stick'){
            if (is_sticky($_POST['update_id'])){
                echo 11;
                unstick_post($_POST['update_id']);
            }
            else{
                $posts = new WP_Query(array(
                    'post_type'      => array( 'post' ),
                    'orderby'        => 'modified',
                    'posts_per_page' => -1,
                    'author'         => get_current_user_id()
                ));
                foreach($posts->posts as $post){
                    unstick_post($post->ID);
                }
                stick_post($_POST['update_id']);
                echo 12;
            }
        }
        else{
            echo 6;
        }
    }
    else if (isset($_POST['update']) && $_POST['update'] == ''){
        echo 6;
    }
    else if ($_POST['update_id'] == 'new'){
	    $new_post = array(
	        'post_title'    	=> 'None',
	        'post_content'  	=> htmlspecialchars($_POST['update']),
	        'post_status'   	=> 'publish',
	    );
        wp_insert_post($new_post);
        echo 2;
    }
    else if (get_post($_POST['update_id']) != null && get_post($_POST['update_id'])->post_author == get_current_user_id()){
        /**$edit_post = array(
            'ID'                => $_POST['update_id'],
            'post_content'      => htmlspecialchars($_POST['update']),
        );
        wp_update_post($edit_post);
        echo 4;**/
        echo 6;
    }
    else{
    	echo 6;
    }
    exit;
}