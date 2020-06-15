<?php

function word_counts($postid, $post_obj){
	//Add Word Count
	update_post_meta($postid,'word-count', str_word_count(strip_tags($post_obj->post_content)));
	
	$chapters = published_chapters($post_obj->post_parent);
	//Add Book Word Count
	$count = 0;
	foreach ($chapters as $chapter){
		$count += (int) get_post_meta($chapter->ID,'word-count',true);
	}
	update_post_meta($post_obj->post_parent,'word-count', $count);

	//Update Time of book according to chapter time
	$book_args = array(
		'ID' => 				$post_obj->post_parent,
		'post_modified' => 		$post_obj->post_modified,
		'post_modified_gmt' => 	$post_obj->post_modified_gmt
	);
	wp_update_post($book_args);
}
add_action('save_post_chapter', 'word_counts', 11,2);

function ring_notify($new_status,$old_status,$post){
	if ($new_status == 'publish' && $post->post_type == 'chapter'){
		//add_collection_notifications($post->ID);
	}
}
add_action('transition_post_status','ring_notify',15,3);