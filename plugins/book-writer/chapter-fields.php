<?php

function chapter_word_count($postid, $post_obj){
	//Add Word Count
	update_post_meta($postid,'word-count', str_word_count(strip_tags($post_obj->post_content)));
	
	//Add Chapter Order
	$chapter_num = count(get_posts(array('post_type'=>'chapter','meta_key'=>'chapter_order','orderby'=>'meta_value_num','order'=>'ASC','post_status'=>array('publish','future'),'post_parent'=>$post_obj->post_parent,'exclude'=>$postid)))+1;
	update_post_meta($postid,'chapter_order',$chapter_num);
}
add_action('publish_chapter', 'chapter_word_count', 11,2);
function save_chapter_validations($post_id,$post_obj,$update ){
	//Unpublish book with no chapters
	if (empty(get_posts(array('post_type'=>'chapter','post_status'=>array('publish'),'post_parent'=>$post_obj->post_parent)))){
		remove_action( "save_post_chapter",'save_chapter_validations',10,3);
		wp_update_post(array(
			'ID'                => $post_obj->post_parent,
			'post_status'   	=> 'draft',
		));
		add_action( "save_post_chapter",'save_chapter_validations',10,3);
	}

	//Draft Chapter if book is not published
	$updated = get_post($post_id);
	if (get_post($updated->post_parent)->post_status == 'draft'){
		remove_action( "save_post_chapter",'save_chapter_validations',10,3);
		wp_update_post(array(
			'ID'                => $updated->ID,
			'post_status'   	=> 'draft',  			
		));
		add_action( "save_post_chapter",'save_chapter_validations',10,3);
	}
}
add_action( "save_post_chapter",'save_chapter_validations',10,3);
function ring_notify($new_status,$old_status,$post){
	if ($new_status == 'publish' && $post->post_type == 'chapter'){
		add_collection_notifications($post->ID);
	}
}
add_action('transition_post_status','ring_notify',15,3);
////Adds Book Count
function add_word_count($postid, $post_obj){
	$args = array(
		'post_type' 	=> 'chapter',
		'post_status' 	=> 'publish',
		'post_parent'	=> $post_obj->post_parent,
		'posts_per_page'=> -1,
	);
	$chapters = get_posts($args);
	if (empty($chapters)){
		update_post_meta($post_obj->post_parent,'word-count',0);
	}
	else{
		$count = 0;
		foreach ($chapters as $chapter)
		{$count += get_post_meta($chapter->ID,'word-count')[0];}
		update_post_meta($post_obj->post_parent,'word-count', $count);
		
	}
}
add_action('publish_chapter', 'add_word_count', 12,2);


function modify_book_modified_date($postid, $post_obj)
{
	remove_action('publish_chapter', 'modify_book_modified_date', 10,2);
	wp_update_post(array(
		'ID' => $post_obj->post_parent,
		'post_modified' => $post_obj->post_modified,
		'post_modified_gmt' => $post_obj->post_modified_gmt
	));
	add_action('publish_chapter', 'modify_book_modified_date', 10,2);
}
add_action('publish_chapter', 'modify_book_modified_date', 10,2);
