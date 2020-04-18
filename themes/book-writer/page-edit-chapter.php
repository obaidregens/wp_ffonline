<?php
/**
 * Template Name: Edit Chapter
 *
 * Allow users to Edit Chapters.
 *
 */
?>
<?php
get_header();
?><main><?php
    $chapter = get_post($_GET['edit_chap']);
    if (get_current_user_id() != $chapter->post_author){
        get_template_part('404');
    }
    else{
        ?> <div class="mobile-margin"> <?php
        
            ?>
            <h4 class="section"><?php echo 'Book: '; if (preg_replace('/\s+/', '',get_post($chapter->post_parent)->post_title) == ''){echo "(No Book Title)";}else{echo get_post($chapter->post_parent)->post_title;}?></h4>
    
            <?php
        	edit_chapter($chapter);
    	?> </div> <?php
    }
?></main><?php
get_sidebar();
get_footer();
?>
<script type = "text/javascript">
function change_publish_label(id){
	if (document.querySelector("#publish-label-" + id).innerHTML == 'Publish'){
		document.querySelector("#publish-label-" + id).innerHTML = 'Draft';
		jQuery("#schedule-switch").prop('disabled', true);
		jQuery("#schedule-switch").prop('checked', false);
		jQuery('#scheduled-input-wrapper').children().remove();
	}
	else{
		document.querySelector("#publish-label-" + id).innerHTML = 'Publish';
		jQuery("#schedule-switch").prop('disabled', false);
	}
}
</script>