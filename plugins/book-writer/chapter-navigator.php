<?php
//Chapter Navigation
// Call this function from 'single-chapter.php' to use for chapter navigation
// Usable for displaying any number of times,
function chapter_navigator()
{
	global $post;
	$chapter_num = get_post_meta($post->ID,'chapter_order')[0];
	$all_chapters = get_posts(array(
		'post_type'		=> 'chapter',
		'posts_per_page'=> -1,
		'meta_key'		=> 'chapter_order',
		'orderby'		=> 'meta_value_num',
		'order'			=> 'ASC',
		'post_status'	=> array('publish','draft'),
		'post_parent'	=> $post->post_parent
	));
	for ($x = $chapter_num-2; $x >= 0; $x--) {
		if ($all_chapters[$x]->post_status == 'publish'){
			$prev_chapter = $all_chapters[$x];
			break;
		}
	}
	for ($x = $chapter_num; $x < count($all_chapters); $x++) {
		if ($all_chapters[$x]->post_status == 'publish'){
			$next_chapter = $all_chapters[$x];
			break;
		}
	}
	if (isset($prev_chapter) && isset($next_chapter)){
	    ?>
	    <div class="row valign-wrapper" style="z-index:50;margin:0;position:fixed;bottom:0;left:0;height:35px;width:100%;background-color:var(--theme-color);">
	        <?php if(isset($prev_chapter)){ ?><a style="margin:0;padding-left:10px;color:white !important;" href="<?php echo get_permalink($prev_chapter->ID); ?>" class="col s6 left-align"><i style="padding-right:10px;" class="fas fa-arrow-left"></i><?php echo $prev_chapter->post_title; ?></a><?php } else{echo '<div class="col s6"></div>';} ?>
	        <?php if(isset($next_chapter)){ ?><a style="margin:0;padding-right:10px;color:white !important;" href="<?php echo get_permalink($next_chapter->ID); ?>" class="col s6 right-align"><?php echo $next_chapter->post_title; ?><i style="padding-left:10px;" class="fas fa-arrow-right"></i></a><?php } ?>
	    </div>
	    <?php
	}
	
}