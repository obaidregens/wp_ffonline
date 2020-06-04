<?php
/**
 * Plugin Name: 1-Stats
 * Description: A plugin by Fanfiction Online, made for statistics and monitoring book activity.
 * Version: 1.0
 * Author: Fanfiction Online
 * Author URI: https://www.fanfiction.online
 */

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
