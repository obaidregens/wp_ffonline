<?php

	$chain_links = count(page_chain_arr);
	if ($chain_links === 2){
		//Book Page
		if (is_edit_valid(page_chain_arr[1],'book')){
			get_template_part('dashboard/part','edit-book');
			exit();
		}
	}
	else if ($chain_links === 3){
		//Chapter Page
		if (is_edit_valid(page_chain_arr[2],'chapter',page_chain_arr[1])){
			get_template_part('dashboard/part','edit-chapter');
			exit();
		}
	}
	//else continue
	$books = array(
		'author__in'	=> array(get_current_user_id()),
		'post_type'		=> 'book',
		'orderby'		=> 'modified',
		'post_status'	=> array('publish','draft'),
		'order'			=> 'DESC',
		'posts_per_page'=> 10,
	);
	if (isset($_GET['page']) && is_numeric($_GET['page'])){
		$books['paged'] = intval($_GET['page']);
	}
	$books = (new WP_Query( $books ))->posts;
	?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<ul class="collapsible">
				<?php foreach($books as $book){?>
					<li>
						<div class="collapsible-header" style="display:block;"><?php if (preg_replace('/\s+/', '',$book->post_title) == ''){echo "(No Book Title)";}else{echo $book->post_title;}?>
							<label style="padding-left:10px;"><a href="/dashboard/write/<?php echo $book->ID; ?>">Edit</a><?php if ($book->post_status == 'publish'){?> / <a href="<?php echo get_permalink($book->ID); ?>">View</a><?php } ?> / <a onclick="delete_this_book(<?php echo $book->ID; ?>)" style="color:#cf0a0a !important;cursor:pointer;">Delete</a></label>
							<label class="right"><?php if ($book->post_status == 'publish'){echo 'Published';}else if ($book->post_status == 'draft'){echo 'Draft';} ?></label>
						</div>
						<div class="collapsible-body">
							<?php
							$chapters = get_posts(array(
								'authors'			=> get_current_user_id(),
								'post_type'			=> 'chapter',
								'post_status'		=> array('publish','draft','future'),
								'post_parent'		=> $book->ID,
								'posts_per_page'	=> -1
							)); ?>
							<ul class="collapsible">
								<?php foreach($chapters as $chapter) { ?>
									<li>
										<div class="collapsible-header" style="display:block;"><?php if (preg_replace('/\s+/', '',$chapter->post_title) == ''){echo "(No Chapter Title)";}else{echo $chapter->post_title;}?>
											<label style="padding-left:10px;"><a onclick="load_page('write/<?php echo $book->ID; ?>/<?php echo $chapter->ID; ?>')" >Edit</a></label>
											<label class="right"><?php if ($book->post_status == 'draft'){} else if ($chapter->post_status == 'publish'){echo 'Published';}else if ($chapter->post_status == 'draft'){echo 'Draft';}else if ($chapter->post_status == 'future'){echo 'Scheduled';}?></label>
										</div>
									</li>
								<?php } ?>
								<li>
									<div onclick="load_page('write/<?php echo $book->ID; ?>/new')" class="collapsible-header"><i class="large material-icons">add</i><a>New Chapter</a></div>
								</li>
							</ul>
						</div>
					</li>
				<?php } ?>
				<li>
					<div onclick="window.location = '/dashboard/write/new'" href="/dashboard/write/new" class="collapsible-header"><i class="large material-icons">add</i><a>New Book</a></div>
				</li>
			</ul>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
	
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
    	api('delete_book',{
    		data: {id:book_id},
    		callback: function(response){
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