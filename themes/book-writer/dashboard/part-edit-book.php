<main><a onclick="load_page('write')" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a><?php
    if (!isset($_POST['id'])){
        ?><script>load_page('dashboard');</script><?php
    }
    else if ($_POST['id'] != 'new'){
        $book = get_post($_POST['id']);
        if (get_current_user_id() != $book->post_author){
            ?><script>load_page('dashboard');</script><?php
        }
        else{
            ?><div class="mobile-margin">
                <h4 class="section"></h4>
        
                <?php
            	edit_book($book);
        	?></div><?php            
        }
    }
    else{
        $book = 'new';
        ?><div class="mobile-margin">
            <h4 class="section"></h4>
    
            <?php
        	edit_book($book);
    	?></div><?php  
    }

?></main>