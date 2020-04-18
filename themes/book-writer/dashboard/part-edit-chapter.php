<main><a onclick="load_page('write')" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a><?php
    $chapter = get_post($_GET['edit_chap']);
    if(strpos($_GET['edit_chap'],'new-') !== false){
        $book_id = explode('-',$_GET['edit_chap'])[1];
        $book = get_post($book_id);
        if (get_current_user_id() != $book->post_author || $book->post_type != 'book'){
            ?><script>load_page('dashboard');</script><?php
        }
        else{
            ?><div class="mobile-margin">
                <h4 class="section"><?php echo 'Book: '; if (preg_replace('/\s+/', '',$book->post_title) == ''){echo "(No Book Title)";}else{echo $book->post_title;}?></h4>
                <?php
            	edit_chapter($_GET['edit_chap']);
        	?></div><?php            
        }
    }
    else if (get_current_user_id() != $chapter->post_author || $chapter->post_type != 'chapter'){
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