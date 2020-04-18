<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( 'POST' == $_SERVER['REQUEST_METHOD']) {
    $_POST['tag'] = explode(',',$_POST['tag']);
    $_POST['category'] = $_POST['fandom'];
	//Pairing START
	$array = array();
	foreach ($_POST as $key => $value) {
		if (strpos($key, 'pairing_') === 0) {
			$key = explode('-',$key)[0];
			$array[$key] = $value;
		}
	}
	
	$pairings = array();
	for ($x = 1; $x <= count($array); $x++) {
		sort($array['pairing_' . $x]);
		$current_pair = '';
		for ($y = 0; $y < count($array['pairing_' . $x]); $y++) {
			if ($y != 0){
				$current_pair .= '/';
			}
			$current_pair .= $array['pairing_' . $x][$y];
		}
		array_push($pairings,$current_pair);
	}
	//Pairing END
    // Required Fields
    $title =  $_POST['book_title'];
	//Optional Fields
	$summary = $_POST['book_description'];
	$description = $_POST['detailed_description'];
	$comments = $_POST['comments'];
	if(isset($comments) == false){
		$comments = 'closed';
	}
    // Add the content of the form to $post as an array
    $new_post = array(
        'post_title'    	=> $title,
        'post_content'  	=> $description,
        'post_status'   	=> 'draft',  
        'post_type'			=> 'book',
		'post_excerpt'		=> $summary,
		'comment_status'	=> $comments,
    );
    //save the new post and return its ID
    $post_id = wp_insert_post($new_post);
	$taxonomies = array('tag','category','rating','language','status','genre');
	foreach($taxonomies as $taxonomy){
		wp_set_object_terms($post_id,$_POST[$taxonomy], $taxonomy);
	}
	$terms_char = array();
	foreach($_POST as $key => $val){
		if (strpos($key,'character_') !== false){
			$parent = get_term_by('slug',str_replace('character_','',$key),'category')->term_id;
			$val_arr = explode(',',$val);
			foreach($val_arr as $term){
				if (term_exists($term,'character',$parent) === null){
					wp_insert_term($term,'character',array('parent'=>$parent));
				}
				$terms_char[] = $term;
			}
		}
	}
	wp_set_object_terms($post_id,$terms_char, 'character');
	wp_set_object_terms($post_id,$pairings, 'pairing');
	update_post_meta($post_id,'simplefavorites_count',0);
}
wp_redirect('https://www.fanfiction.online/dashboard/write');
exit();