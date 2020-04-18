<?php
define('WP_USE_THEMES', false);
require('/home3/eshaatco/public_html/fic/wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['to']) && isset($_POST['message']) ){
    // Add the content of the form to $post as an array
    $new_post = array(
        'post_title'    	=> 'None',
        'post_content'  	=> $_POST['message'],
        'post_status'   	=> 'publish',  
        'post_type'			=> 'message',
    );
    //save the new post and return its ID
    $post_id = wp_insert_post($new_post);
    wp_set_object_terms($post_id,'Sent', 'message_status');
    if(get_current_user_id() == $_POST['to']){
        wp_set_object_terms($post_id,array($_POST['to']), 'message_between');
    }
    else{
        wp_set_object_terms($post_id,array(strval(get_current_user_id()),strval($_POST['to'])), 'message_between');
    }
    echo $post_id . ',' . get_post($post_id)->post_date;
    
 exit;
}