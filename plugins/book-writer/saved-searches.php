<?php
function save_search($name,$link){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		update_user_meta(get_current_user_id(),'saved_searches',array(array($name,$link)));
		return;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	if (saved_search_exists($name,$link) != false){
		return;
	}
	$searches[] = array($name,$link);
	update_user_meta(get_current_user_id(),'saved_searches',$searches);
}
function saved_search_exists($name,$link){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return false;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	if (in_array($link,array_column($searches,1))){
		return 'Search exists.';
	}
	else if (in_array($name,array_column($searches,0))){
		return 'Duplicate name.';
	}
	else{
		return false;
	}
}
function get_saved_searches(){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return array();
	}
	return get_user_meta(get_current_user_id(),'saved_searches',true);
}
function delete_search($name_or_link){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	foreach($searches as $key => $search){
		if ($search[0] == $name_or_link || $search[1] == $name_or_link){
			unset($searches[$key]);
			$searches = array_values($searches);
			update_user_meta(get_current_user_id(),'saved_searches',$searches);
			return;
		}
	}
}