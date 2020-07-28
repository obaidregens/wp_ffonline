<?php
$args = array (
    'post_type' 	=> 'chapter',
	'post_parent' 	=> $post-> post_parent,
	'selected' 		=> $post->ID,
	'class'	 		=> 'postform',
	'post_status' 	=> 'publish',
	'direct'     	=> true,
	'number'		=> true,
	'meta_key'		=> 'chapter_order',
	'orderby'		=> 'meta_value_num',
	'order'			=> 'ASC',
    );
wp_dropdown_posts($args);
