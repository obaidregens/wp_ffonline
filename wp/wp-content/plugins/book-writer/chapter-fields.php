<?php
function word_counts($postid, $post_obj){
	$chapters = published_chapters($post_obj->post_parent,-1,'ids');
	//Add Book Word Count
	$count = 0;
	foreach ($chapters as $chapter_id){
		$count += (int) get_post_meta($chapter_id,'word-count',true);
	}
	if (! in_array($postid, $chapters) && $post_obj->post_status === 'publish'){
		$count += $this_word_count;
	}
	update_post_meta($post_obj->post_parent,'word-count', $count);
}
add_action('save_post_chapter', 'word_counts', 11,2);