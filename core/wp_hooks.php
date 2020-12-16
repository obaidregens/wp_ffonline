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
remove_filter( 'pre_term_name', 'sanitize_text_field' );
remove_filter( 'pre_term_name', 'wp_filter_kses' );
remove_filter( 'pre_term_name', '_wp_specialchars', 30 );

// Author Link
add_filter( 'author_link', function($link,$author_id,$author_nicename){
	return home_url( '@' ) . get_the_author_meta( 'user_login', $author_id );
}, 10, 3 );

add_filter('flush_rewrite_rules_hard','__return_false');

function time_format_change($time,$format,$post){
	return human_time_diff(get_post_modified_time('U',false,$post));
}
add_filter( 'get_date', "time_format_change", 100, 3);
add_filter( 'get_the_date', "time_format_change", 100, 3);
add_filter( 'get_the_time', "time_format_change", 100, 3);
add_filter( 'post_date_column_time' , 'time_format_change', 100, 3);
function time_form($status){
	return "Updated";
}
add_filter( 'post_date_column_status', 'time_form', 99);
