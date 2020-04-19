<?php
/**
 * Plugin Name: 1-Stats
 * Description: A plugin by Fanfiction Online, made for statistics and monitoring book activity.
 * Version: 1.0
 * Author: Fanfiction Online
 * Author URI: https://www.fanfiction.online
 */
////////////////////////////////////////////// FAVORITES ///////////////////////////////////////////////
function delete_favorite_book($book_id_or_arr){
	if (! is_user_logged_in()){
		return;
	}
	if (! is_array($book_id_or_arr)){
		$books = array($book_id_or_arr);
	}
	else{
		$books = $book_id_or_arr;
	}
	foreach($books as $book_id){
		if(metadata_exists('post',$book_id,'book_favorites')){
			$favorites = get_post_meta($book_id,'book_favorites',true);
			foreach($favorites as $num => $favorite){
				if ($favorite[0] == get_current_user_id()){
					unset($favorites[$num]);
					sort($favorites);
					update_post_meta($book_id,'book_favorites',$favorites);
					break;
				}
			}
		}
		if(metadata_exists('user',get_current_user_id(),'user_favorites')){
			$favorites = get_user_meta(get_current_user_id(),'user_favorites',true);
			foreach($favorites as $num => $favorite){
				if ($favorite[0] == $book_id){
					unset($favorites[$num]);
					sort($favorites);
					update_user_meta(get_current_user_id(),'user_favorites',$favorites);
					break;
				}
			}
		}
	}
}
function add_favorite_book($book_id){
	if (! is_user_logged_in()){
		return;
	}
	$book = get_post($book_id);
	$current_time = current_time('timestamp');
	if ($book === null || $book->post_type != 'book'){
		return;
	}
	if(metadata_exists('post',$book_id,'book_favorites')){
		$favorites = get_post_meta($book_id,'book_favorites',true);
		if (! in_array(get_current_user_id(),array_column($favorites,0))){
			$favorites[] = array(get_current_user_id(),$current_time);
			update_post_meta($book_id,'book_favorites',$favorites);			
		}
	}
	else{
		update_post_meta($book_id,'book_favorites',array(array(get_current_user_id(),$current_time)));
	}
	if(metadata_exists('user',get_current_user_id(),'user_favorites')){
		$favorites = get_user_meta(get_current_user_id(),'user_favorites',true);
		if (! in_array($book_id,array_column($favorites,0))){
			$favorites[] = array($book_id,$current_time);
			update_user_meta(get_current_user_id(),'user_favorites',$favorites);			
		}
	}
	else{
		update_user_meta(get_current_user_id(),'user_favorites',array(array($book_id,$current_time)));
	}
	add_notification('favorited',$book_id,get_current_user_id());
}
function get_stats_of($type,$id,$from = null,$to = null){
	if(! is_user_logged_in()){
		return array();
	}
	if (strpos($type,'_comments')){
		if ($from == null){
			$from = '1971-00-00';
		}
		if($to == null){
			$to = 'now';
		}
		$date_query = array(
			'relation' => 'AND',
			array(
				'after'         => $from,
				'before'		=> $to,
				'inclusive'     => true,
			),
		);
		$args = array(
			'date_query'     => $date_query,
			'fields'		 => 'ids'
		);
		if ($type == 'user_comments'){
			$args['post_author'] = $id;
			$args['author__not_in'] = array(get_current_user_id());
		}	
		else if ($type == 'book_comments'){
			$args['post_id'] = $id;
			$args['author__not_in'] = array(get_current_user_id());
		}
		else if ($type == 'all_comments'){}
		else{
			return;
		}
		$comment_query = new WP_Comment_Query( $args );
		return $comment_query->comments;
	}
	if ($type == 'book_fav'){
		if (! metadata_exists('post',$id,'book_favorites')){
			return array();
		}
		$favorites = get_post_meta($id,'book_favorites',true);		
	}
	else if($type == 'user_fav'){
		if (! metadata_exists('user',$id,'user_favorites')){
			return array();
		}
		$favorites = get_user_meta($id,'user_favorites',true);		
	}
	else if ($type == 'book_hidden'){
		if (! metadata_exists('post',$id,'hidden_users')){
			return array();
		}
		$favorites = get_post_meta($id,'hidden_users',true);		
	}
	else if($type == 'user_hidden'){
		if (! metadata_exists('user',$id,'hidden_books')){
			return array();
		}
		$favorites = get_user_meta($id,'hidden_books',true);		
	}
	else if ($type == 'collection_follow'){
		if (! metadata_exists('term',$id,'users_followed')){
			return array();
		}
		$favorites = get_term_meta($id,'users_followed',true);		
	}
	else if($type == 'user_follow'){
		if (! metadata_exists('user',$id,'collections_followed')){
			return array();
		}
		$favorites = get_user_meta($id,'collections_followed',true);		
	}
	else if ($type == 'views'){
		if (! metadata_exists('post',$id,'book_external_views')){
			return array();
		}
		$favorites = get_post_meta($id,'book_external_views',true);		
	}
	else if($type == 'clicks'){
		if (! metadata_exists('post',$id,'book_clicks')){
			return array();
		}
		$favorites = get_post_meta($id,'book_clicks',true);		
	}
	else if($type == 'impressions'){
		if (! metadata_exists('post',$id,'book_impressions')){
			return array();
		}
		$favorites = get_post_meta($id,'book_impressions',true);		
	}
	else if($type == 'notifications'){
		if(! metadata_exists('user',$id,'user_notification_list')){
			return array();
		}
		$favorites = get_user_meta($id,'user_notification_list',true);
		$favorites = array_reverse($favorites);
	}
	else{
		return array();
	}
	if (empty($favorites)){
		return array();
	}
	if ($from == null && $to == null){
		return array_column($favorites,0);
	}
	else{
		if ($from == null){
			$from = '1971-00-00';
		}
		else if($to == null){
			$to = 'now';
		}
		$from_stamp = strtotime($from);
		$to_stamp = strtotime($to);
		$stamps = array_column($favorites,1);
		$selected_favs = array();
		foreach ($stamps as $num => $stamp){
			if ($stamp >= $from_stamp && $stamp <= $to_stamp){
				$selected_favs[] = $favorites[$num][0];
			}
		}
		return $selected_favs;
	}
}
function get_click_rate($id,$from = null,$to = null){
	$clicks = count(get_stats_of('clicks',$id,$from,$to));
	$impressions = count(get_stats_of('impressions',$id,$from,$to));
	if ($clicks == 0 || $impressions == 0){
		return 0;
	}
	else{
		return $clicks/$impressions*100;
	}
}
//////////////////////////////////////////// VIEWS/CLICKS //////////////////////////////////////////////
function record_landing(){
	if (! headers_sent() && ! isset($_SESSION) ){
		session_start();
	}
 	global $template;
	$template_pages = str_replace("/home3/eshaatco/public_html/fic/wp-content/themes/book-writer/","",$template);
	if ($template_pages = 'single-chapter.php' || ($template_pages == 'single-book.php' && isset($_GET['chapter']) && is_numeric($_GET['chapter']))){
		global $post;
		$id = $post->post_parent;
	}
	else if ($template_pages == 'single-book.php'){
		global $post;
		$id = $post->ID;
	}
	else{
		return;
	}
	if(!isset($_SESSION['book_views_clicks'])){
		$_SESSION['book_views_clicks'] = array();
	}
	if (in_array($id,$_SESSION['book_views_clicks'])){
		return;
	}
	if (strpos('https://fanfiction.online/',$_SERVER['HTTP_REFERER'])){
		$post_meta_name = 'book_clicks';
		$user_meta_name = 'user_clicks';
	}
	else{
		$post_meta_name = 'book_external_views';
		$user_meta_name = 'user_external_views';
	}
	$current_time = current_time('timestamp');
	if (metadata_exists('post',$id,$post_meta_name)){
		$post_meta_arr = get_post_meta($id,$post_meta_name,true);
		$post_meta_arr[] = array(get_current_user_id(),$current_time);
		update_post_meta($id,$post_meta_name,$post_meta_arr);
	}
	else{
		update_post_meta($id,$post_meta_name,array(array(get_current_user_id(),$current_time)));
	}
	if (is_user_logged_in()){
		if (metadata_exists('user',get_current_user_id(),$user_meta_name)){
			$user_meta_arr = get_user_meta(get_current_user_id(),$user_meta_name,true);
			$user_meta_arr[] = array($id,$current_time);
			update_user_meta(get_current_user_id(),$user_meta_name,$user_meta_arr);
		}
		else{
			update_user_meta(get_current_user_id(),$user_meta_name,array(array($id,$current_time)));
		}
	}
	$_SESSION['book_views_clicks'][] = $id;
}
/////////////////////////////////////////// IMPRESSIONS ////////////////////////////////////////////////
function record_impressions(){
	if (! headers_sent() && ! isset($_SESSION) ){
		session_start();
	}
	global $post;
	$id = $post->ID;
	if(!isset($_SESSION['book_impressions'])){
		$_SESSION['book_impressions'] = array();
	}
	if (in_array($id,$_SESSION['book_impressions'])){
		return;
	}
	$current_time = current_time('timestamp');
	if (metadata_exists('post',$id,'book_impressions')){
		$post_meta_arr = get_post_meta($id,'book_impressions',true);
		$post_meta_arr[] = array(get_current_user_id(),$current_time);
		update_post_meta($id,'book_impressions',$post_meta_arr);
	}
	else{
		update_post_meta($id,'book_impressions',array(array(get_current_user_id(),$current_time)));
	}
	if (is_user_logged_in()){
		if (metadata_exists('user',get_current_user_id(),'user_impressions')){
			$user_meta_arr = get_user_meta(get_current_user_id(),'user_impressions',true);
			$user_meta_arr[] = array($id,$current_time);
			update_user_meta(get_current_user_id(),'user_impressions',$user_meta_arr);
		}
		else{
			update_user_meta(get_current_user_id(),'user_impressions',array(array($id,$current_time)));
		}
	}
	$_SESSION['book_impressions'][] = $id;	
}
//////////////////////////////////////////// BOOKMARKS /////////////////////////////////////////////////
function add_chapter_bookmark($chapter_id,$para){
	if (! is_user_logged_in()){
		return;
	}
	$chapter = get_post($chapter_id);
	$current_time = current_time('timestamp');
	if ($chapter === null || $chapter->post_type != 'chapter'){
		return;
	}
	if(metadata_exists('user',get_current_user_id(),'user_bookmarks')){
		$bookmarks = get_user_meta(get_current_user_id(),'user_bookmarks',true);
		$exists = false;
		foreach($bookmarks as $num => $bookmark){
			if ($bookmark[0] == $chapter_id && $bookmark[1] == $para){
				$exists = true;
				break;
			}
		}
		if ($exists == false){
			$bookmarks[] = array($chapter_id,$para,$current_time);
			update_user_meta(get_current_user_id(),'user_bookmarks',$bookmarks);			
		}
	}
	else{
		update_user_meta(get_current_user_id(),'user_bookmarks',array(array($chapter_id,$para,$current_time)));
	}
}
function get_chapter_bookmarks($from = null,$to = null){
	if (! is_user_logged_in()){
		return array();
	}
	if (! metadata_exists('user',get_current_user_id(),'user_bookmarks')){
		return array();
	}
	$bookmarks = get_user_meta(get_current_user_id(),'user_bookmarks',true);		
	if (empty($bookmarks)){
		return array();
	}
	if ($from == null && $to == null){
		return $bookmarks;
	}
	else{
		if ($from == null){
			$from = '1971-00-00';
		}
		else if($to == null){
			$to = 'now';
		}
		$from_stamp = strtotime($from);
		$to_stamp = strtotime($to);
		$stamps = array_column($bookmarks,2);
		$selected_bookmarks = array();
		foreach ($stamps as $num => $stamp){
			if ($stamp >= $from_stamp && $stamp <= $to_stamp){
				$selected_bookmarks[] = $favorites[$num];
			}
		}
		return $selected_bookmarks;
	}	
}
function delete_chapter_bookmark($chapter_id,$para){
	if (! is_user_logged_in()){
		return;
	}
	$chapter = get_post($chapter_id);
	if(metadata_exists('user',get_current_user_id(),'user_bookmarks')){
		$bookmarks = get_user_meta(get_current_user_id(),'user_bookmarks',true);
		foreach($bookmarks as $num => $bookmark){
			if ($bookmark[0] == $chapter_id && $bookmark[1] == $para){
				unset($bookmarks[$num]);
				sort($bookmarks);
				update_user_meta(get_current_user_id(),'user_bookmarks',$bookmarks);
				return;
			}
		}
	}
	else{
		return;
	}
}
function chapter_bookmark_exists($chapter_id,$para){
	if (! is_user_logged_in()){
		return false;
	}
	$chapter = get_post($chapter_id);
	if ($chapter === null || $chapter->post_type != 'chapter'){
		return false;
	}
	if (! metadata_exists('user',get_current_user_id(),'user_bookmarks')){
		return false;
	}
	$bookmarks = get_user_meta(get_current_user_id(),'user_bookmarks',true);
	$exists = false;
	foreach($bookmarks as $num => $bookmark){
		if ($bookmark[0] == $chapter_id && $bookmark[1] == $para){
			$exists = true;
		}
	}
	return $exists;
}
//////////////////////////////////////////// FOLLOW COLLECTIONS ////////////////////////////////////////
function delete_follow_collection($collection_id_or_arr){
	if (! is_user_logged_in()){
		return;
	}
	if (! is_array($collection_id_or_arr)){
		$collections = array($collection_id_or_arr);
	}
	else{
		$collections = $collection_id_or_arr;
	}
	foreach($collections as $collection_id){
		if(metadata_exists('term',$collection_id,'users_followed')){
			$followed = get_term_meta($collection_id,'users_followed',true);
			foreach($followed as $num => $follow){
				if ($follow[0] == get_current_user_id()){
					unset($followed[$num]);
					sort($followed);
					update_term_meta($collection_id,'users_followed',$followed);
					break;
				}
			}
		}
		if(metadata_exists('user',get_current_user_id(),'collections_followed')){
			$followed = get_user_meta(get_current_user_id(),'collections_followed',true);
			foreach($followed as $num => $follow){
				if ($follow[0] == $collection_id){
					unset($followed[$num]);
					sort($followed);
					update_user_meta(get_current_user_id(),'collections_followed',$followed);
					break;
				}
			}
		}
	}
}
function add_follow_collection($collection_id){	
	if (! is_user_logged_in()){
		return;
	}
	$collection = get_term($collection_id);
	$current_time = current_time('timestamp');
	if ($collection === null || $collection->taxonomy != 'collection'){
		return;
	}
	if(metadata_exists('term',$collection_id,'users_followed')){
		$collections = get_term_meta($collection_id,'users_followed',true);
		if (! in_array(get_current_user_id(),array_column($collections,0))){
			$collections[] = array(get_current_user_id(),$current_time);
			update_term_meta($collection_id,'users_followed',$collections);			
		}
	}
	else{
		update_term_meta($collection_id,'users_followed',array(array(get_current_user_id(),$current_time)));
	}
	if(metadata_exists('user',get_current_user_id(),'collections_followed')){
		$collections = get_user_meta(get_current_user_id(),'collections_followed',true);
		if (! in_array($collection_id,array_column($collections,0))){
			$collections[] = array($collection_id,$current_time);
			update_user_meta(get_current_user_id(),'collections_followed',$collections);			
		}
	}
	else{
		update_user_meta(get_current_user_id(),'collections_followed',array(array($collection_id,$current_time)));
	}
}
//////////////////////////////////////////////// NOTIFICATIONS //////////////////////////////////////////
function add_collection_notifications($chapter_id){
	if (! is_user_logged_in()){
		return;
	}
	$chapter = get_post($chapter_id);
	$collections = wp_get_object_terms($chapter->post_parent,'collection');
	foreach ($collections as $collection){
	    $followers = get_stats_of('collection_follow',$collection->term_id);
	    foreach($followers as $user){
	        add_notification('followed',$chapter_id,$collection->term_id,$user);
	    }
	}
	$favorites_of = get_stats_of('book_fav',$chapter->post_parent);
	foreach($favorites_of as $user){
	    add_notification('followed',$chapter_id,'favorites',$user);
	}
	$hidden_of = get_stats_of('book_hidden',$chapter->post_parent);
	foreach($hidden_of as $user){
	    add_notification('followed',$chapter_id,'hidden',$user);
	}
    
}
// string | $type = 'message' || 'followed' || 'comment' || 'favorited'
// int	  | $id = //ID of either message, new chapter, comment or favorited book
// int    | $extra = //Only used if $type = 'followed' or 'favorited'
//		FOLLOWED
//		-> ID of collection
//		FAVORITED
//		-> ID of user favorited
// int    | $following_user = //Only used if $type = 'followed
function add_notification($type,$id,$extra = null,$following_user = null){
	if (! is_user_logged_in()){
		return;
	}
	if (($type == 'followed' || $type == 'favorited') && $extra == null){
		return;
	}
	if ($type == 'followed' && $following_user == null){
		return;
	}
	switch ($type) {
		case "message":
			$message = get_post($id);
			if ($message === null || $message->post_type != 'message'){
				return;
			}
			$msg_bw = wp_get_object_terms($id,'message_between',array('fields'=>'names'));
			unset($msg_bw[array_search(strval($message->post_author),$msg_bw)]);
			$notification_of = array_values($msg_bw)[0];

			break;
		case "followed":
			$chapter = get_post($id);
			if ($chapter === null || $chapter->post_type != 'chapter'){
				return;
			}
			if ($extra != 'hidden' && $extra != 'favorites'){
				$extra_obj = get_term($extra);
				if ($extra_obj === null){
					return;
				}				
			}
			$notification_of = $following_user;
			break;
		case "comment":
			$comment = get_comment($id);
			if ($comment === null){
				return;
			}
			$notification_of = get_post($comment->comment_post_ID)->post_author;
			break;
		case "favorited":
			$book = get_post($id);
			if ($book === null || $book->post_type != 'book'){
				return;
			}
			$extra_obj = get_userdata($extra);
			if ($extra_obj == false){
				return;
			}
			$notification_of = $book->post_author;
			break;
		default:
			return;
	}
	$data = array('type'=>$type,'id'=>$id,'extra'=>$extra);
	$current_time = current_time('timestamp');
	if(metadata_exists('user',$notification_of,'user_notification_list')){
		$notifications = get_user_meta($notification_of,'user_notification_list',true);
		$notifications[] = array($data,$current_time);
		update_user_meta($notification_of,'user_notification_list',$notifications);
	}
	else{
		update_user_meta($notification_of,'user_notification_list',array(array($data,$current_time)));
	}
	notification_emails($type,$id,$extra,$notification_of);
}
function notification_emails($type,$id,$extra,$notification_of){
    if(get_user_meta($notification_of,'email_me_notifications',true) == 'false'){
        return;
    }
	switch ($type) {
		case "message":
		    $obj = get_post($id);
		    $author_name = get_the_author_meta('display_name',$obj->user_id);
		    $subject = $author_name . ' just messaged you.';
        	$message = $author_name . ' just messaged you.' . "\r\n";
        	$message .= get_permalink($id) . "\r\n\r\n";
			break;
		case "followed":
		    $chapter = get_post($id);
        	$book = get_post($chapter->post_parent);
        	$author_name = get_the_author_meta('display_name',$book->post_author);
        	if ($extra != 'hidden' && $extra != 'favorites'){
        	    $collection = get_term($extra)->name;
        	}
        	else{
        	    $collection_name = ucfirst($extra);
        	}
        	$subject = $book->post_title . ' by ' . $author_name . ' just got a new chapter!';
        	$message = $book->post_title . ' by ' . $author_name . 'from your collection \'' . $collection_name .  '\' just got a new chapter!' . "\r\n\r\n";
            $message .=  'Chapter ' . get_post_meta($chapterid,'chapter_order',true) . ': ' . $chapter-> post_title  . "\r\n";
        	$message .= get_permalink($chapter->ID) . "\r\n\r\n";
			break;
		case "comment":
			$comment = get_comment($id);
			$author_name = get_the_author_meta('display_name',$comment->user_id);
			$chapter_on = get_post($comment->comment_post_ID);
		    $subject = 'New comment on your chapter \'' . $chapter_on->post_title . '\'.';
        	$message = $author_name . ' commented on your chapter \'' . $chapter_on->post_title . '\'.' . "\r\n";
        	$message .= get_comment_link($id) . "\r\n\r\n";
			break;
		case "favorited":
			$book = get_post($id);
			$user_name = get_the_author_meta('display_name',$extra);
		    $subject = 'Your book \'' . $book->post_title . '\' has a new favorite.';
        	$message = 'Your book \'' . $book->post_title . '\' has been favorited by \'' . $user_name . '\'.' . "\r\n\r\n";
        	$message .= 'See book stats in depth:' . "\r\n";
        	$message .= 'https://fanfiction.online/dashboard/stats' . "\r\n\r\n";
			break;
		default:
			return;
	}
	$message .= "You got this email because you have email notifications enabled, you can disable them here." . "\r\n";
	$message .= "https://fanfiction.online/dashboard/settings" . "\r\n";
	$user_email = get_the_author_meta('user_email',$notification_of);
	wp_mail($user_email,$subject,$message);
}
//////////////////////////////////////////////////// HIDDEN /////////////////////////////////////////////

function add_to_hidden($book_id){
	if (! is_user_logged_in()){
		return;
	}
	$book = get_post($book_id);
	$current_time = current_time('timestamp');
	if ($book === null || $book->post_type != 'book'){
		return;
	}
	if(metadata_exists('post',$book_id,'hidden_users')){
		$favorites = get_post_meta($book_id,'hidden_users',true);
		if (! in_array(get_current_user_id(),array_column($favorites,0))){
			$favorites[] = array(get_current_user_id(),$current_time);
			update_post_meta($book_id,'hidden_users',$favorites);			
		}
	}
	else{
		update_post_meta($book_id,'hidden_users',array(array(get_current_user_id(),$current_time)));
	}
	if(metadata_exists('user',get_current_user_id(),'hidden_books')){
		$favorites = get_user_meta(get_current_user_id(),'hidden_books',true);
		if (! in_array($book_id,array_column($favorites,0))){
			$favorites[] = array($book_id,$current_time);
			update_user_meta(get_current_user_id(),'hidden_books',$favorites);			
		}
	}
	else{
		update_user_meta(get_current_user_id(),'hidden_books',array(array($book_id,$current_time)));
	}
}
function delete_hidden($book_id_or_arr){
	if (! is_user_logged_in()){
		return;
	}
	if (! is_array($book_id_or_arr)){
		$books = array($book_id_or_arr);
	}
	else{
		$books = $book_id_or_arr;
	}
	foreach($books as $book_id){
		if(metadata_exists('post',$book_id,'hidden_users')){
			$favorites = get_post_meta($book_id,'hidden_users',true);
			foreach($favorites as $num => $favorite){
				if ($favorite[0] == get_current_user_id()){
					unset($favorites[$num]);
					sort($favorites);
					update_post_meta($book_id,'hidden_users',$favorites);
					break;
				}
			}
		}
		if(metadata_exists('user',get_current_user_id(),'hidden_books')){
			$favorites = get_user_meta(get_current_user_id(),'hidden_books',true);
			foreach($favorites as $num => $favorite){
				if ($favorite[0] == $book_id){
					unset($favorites[$num]);
					sort($favorites);
					update_user_meta(get_current_user_id(),'hidden_books',$favorites);
					break;
				}
			}
		}
	}
}