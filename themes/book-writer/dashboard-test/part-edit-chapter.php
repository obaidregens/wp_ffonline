<main><a onclick="load_page('write')" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a><?php
    $chapter = get_post($_GET['edit_chap']);
    if (get_current_user_id() != $chapter->post_author){
        ?><script>load_page('dashboard');</script><?php
    }
    else{
        ?><div class="mobile-margin">
            <h4 class="section"><?php echo 'Book: '; if (preg_replace('/\s+/', '',get_post($chapter->post_parent)->post_title) == ''){echo "(No Book Title)";}else{echo get_post($chapter->post_parent)->post_title;}?></h4>
    
            <?php
        	edit_chapter($chapter);
    	?></div><?php
    }
?></main>