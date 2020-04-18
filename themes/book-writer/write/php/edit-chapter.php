<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax']) && isset($_POST['id']) && isset($_POST['title'])) {
	if (! isset($_SESSION)){
		session_start();
	}
    if($_POST['ajax'] != $_SESSION['nonce_key']){
        echo '9';
        exit();
    }
    //'6' is the validation error code
	if (strpos($_POST['id'],'new-') === false){
	    $old = get_post($_POST['id']);
	    if ($old->post_author != get_current_user_id()){
	        echo '6';
	        exit();
	    }
	}
	else{
	    if (get_post(str_replace('new-','',$_POST['id']))->post_author != get_current_user_id()){
	        echo '6';
	        exit();
	    }
	    $old = 'new';
	}
    // Required Fields
    $post_id = $_POST['id'];
    $title =  $_POST['title'];
	$content = strip_tags(stripcslashes($_POST['content']),'<p><strike><b><i><u>');
    if (strlen(preg_replace ('/(((<p>)|(<p)([\s]*?)(style="text-align:)([\s]*?)(center|right|left)(;">))([\s\S]*?)(<\/p>))/i','',$content)) > 0){
        echo '78';
        exit();
    }
	//Optional Fields
	$pre = $_POST['pre'];
	$post = $_POST['post'];
	//Switches
	$publish = $_POST['publish'];
	if ($_POST['date'] != 0){
		$publish = 'future';
	}
    // Add the content of the form to $post as an array
    $new_post = array(
        'post_title'    	=> $title,
        'post_content'  	=> $content,
        'post_status'   	=> $publish,  
		'comment_status'	=> $_POST['comments'],
    );
	if ($_POST['date'] != 0){
		if (strpos($_POST['time'],'PM')){
			$time_arr = explode(':',explode(' PM',$_POST['time'])[0]);
			$time_arr[0] += 12;
		}
		else{
			$time_arr = explode(':',explode(' AM',$_POST['time'])[0]);
		}
		$time_arr[0] += intval($_POST['offset']/60);
		if ($time_arr[0] < 0){
			$time_arr[0] = 24 + $time_arr[0];
			$bubble_d = explode('-',$_POST['date']);
			$bubble_d[2] -= 1;
			$_POST['date'] = $bubble_d[0] . '-' . $bubble_d[1] . '-' . $bubble_d[2];
		}
		
		if( is_float ($_POST['offset']/60)){
			if ($_POST['offset'] > 0){
				if ($time_arr[1] < 30){
					$time_arr[1] += 30;
				}
				else{
					$time_arr[0] += 1;
					$time_arr[1] += 30-60;
				}
			}
			else{
				if ($time_arr[1] >= 30){
					$time_arr[1] -= 30;
				}
				else{
					$time_arr[0] -= 1;
					$time_arr[1] = 60 + $time_arr[1] - 30;
				}				
			}
		}
		$new_post['post_date'] = $_POST['date'] . ' ' . $time_arr[0] . ':' . $time_arr[1] . ':00';
		$new_post['post_date_gmt'] = $new_post['post_date'];
		$new_post['edit_date'] = true;
	}
	else{
		$new_post['post_date'] = current_time('Y-m-d H:i:s',true);
		$new_post['post_date_gmt'] = $new_post['post_date'];
		$new_post['edit_date'] = true;
	}
    //save the new post and return its ID
    //print_r( $new_post);
    if (strpos($post_id,'new-') !== false){
        $new_post['post_parent'] = explode('-',$post_id)[1];
        $new_post['post_type'] = 'chapter';
        $post_id = wp_insert_post($new_post);
    }
    else{
        $new_post['ID'] = $post_id;
        wp_update_post($new_post);
    }
    update_post_meta($post_id,'pre-chapter_notes',$pre);
    update_post_meta($post_id,'post-chapter_notes',$post);
	$updated = get_post($post_id);
	if ($updated->post_status == 'publish'){
		echo '1';
	}
	else if($updated->post_status == 'draft'){
		echo '2';
	}
	else if($updated->post_status == 'future'){
		echo '4';
	}	
	else{
		echo '3';
	}
	exit();
}