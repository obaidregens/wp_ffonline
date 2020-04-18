<?php
	$books = get_pages(array(
		'authors'		=> get_current_user_id(),
		'post_type'		=> 'book',
		'sort_column'	=> 'post_modified',
		'post_status'	=> array('publish','draft'),
		'sort_order'	=> 'DESC'
	)); ?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<ul class="collapsible">
				<?php foreach($books as $book){?>
					<li>
						<div class="collapsible-header" style="display:block;"><?php if (preg_replace('/\s+/', '',$book->post_title) == ''){echo "(No Book Title)";}else{echo $book->post_title;}?>
							<label style="padding-left:10px;"><a href="/dashboard/edit-book?edit-book=<?php echo $book->ID; ?>">Edit</a><?php if ($book->post_status == 'publish'){?> / <a href="<?php echo get_permalink($book->ID); ?>">View</a><?php } ?> / <a onclick="delete_this_book(<?php echo $book->ID; ?>)" style="color:#cf0a0a !important;cursor:pointer;">Delete</a></label>
							<label class="right"><?php if ($book->post_status == 'publish'){echo 'Published';}else if ($book->post_status == 'draft'){echo 'Draft';} ?></label>
						</div>
						<div class="collapsible-body">
							<?php if (count(get_posts(array('post_type'=>'chapter','post_status'=>array('publish','future'),'post_parent'=>$book->ID,'posts_per_page'=>-1))) > 1){ ?>
								<button class="waves-effect waves-light btn-small modal-trigger" style="width:100%" data-target="order-<?php echo $book->ID; ?>">Order Chapters</button>
							<?php }
							$chapters = get_posts(array(
								'authors'		=> get_current_user_id(),
								'post_type'		=> 'chapter',
								'post_status'	=> array('publish','draft','future'),
								'post_parent'	=> $book->ID,
								'posts_per_page'=> -1
							)); ?>
							<ul class="collapsible">
								<?php foreach($chapters as $chapter) { ?>
									<li>
										<div class="collapsible-header" style="display:block;"><?php if (preg_replace('/\s+/', '',$chapter->post_title) == ''){echo "(No Chapter Title)";}else{echo $chapter->post_title;}?>
											<label style="padding-left:10px;"><a onclick="load_page('edit-chapter','<?php echo $chapter->ID; ?>')" >Edit</a></label>
											<label class="right"><?php if ($book->post_status == 'draft'){} else if ($chapter->post_status == 'publish'){echo 'Published';}else if ($chapter->post_status == 'draft'){echo 'Draft';}else if ($chapter->post_status == 'future'){echo 'Scheduled';}?></label>
										</div>
									</li>
								<?php } ?>
								<li>
									<div onclick="load_page('edit-chapter','new-<?php echo $book->ID; ?>')" class="collapsible-header"><i class="large material-icons">add</i><a>New Chapter</a></div>
								</li>
							</ul>
						</div>
					</li>
				<?php } ?>
				<li>
					<div onclick="window.location = '/dashboard/edit-book?edit-book=new'" href="/dashboard/edit-book?edit-book=new" class="collapsible-header"><i class="large material-icons">add</i><a>New Book</a></div>
				</li>
			</ul>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
	
	<?php order_book_modals($books); ?>
<script>
    jQuery(document).ready(function(){
		jQuery('.collapsible').collapsible();
		jQuery('.tooltipped').tooltip();
    });
    function delete_this_book(book_id){
        var confirm = window.confirm("Are you sure you want to delete this book and all it's chapters?");
        if (confirm == false){
            return;
        }
        jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');
    	jQuery.ajax({
    		url: '/wp-content/themes/book-writer/dashboard/ajax/delete-book.php',
    		type: 'post',
    		data: {ajax: 1,id:book_id},
    		success: function(response){
    		    M.Toast.dismissAll();
    			if (response == '1'){
    			    load_page('write');
    				M.toast({html: 'Book Deleted.'});
    			}
    			else{
    				M.toast({html: 'An unknown error occured.'});
    			}
    		}
    	});
    }
</script>