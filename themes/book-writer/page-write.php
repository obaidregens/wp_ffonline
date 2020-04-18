<?php
/* Template Name: Write */ 
?>
<?php
//page_header
get_header(); ?>

<?php if(is_user_logged_in()) {
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
							<label style="padding-left:10px;"><a class="modal-trigger" data-target="<?php echo $book->ID; ?>">Edit</a><?php if ($book->post_status == 'publish'){?> / <a href="<?php echo get_permalink($book->ID); ?>">View</a><?php } ?></label>
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
											<label style="padding-left:10px;"><a href="edit-chapter/?edit_chap=<?php echo $chapter->ID; ?>">Edit</a></label>
											<label class="right"><?php if ($chapter->post_status == 'publish'){echo 'Published';}else if ($chapter->post_status == 'draft'){echo 'Draft';}else{echo 'Scheduled';}?></label>
										</div>
									</li>
								<?php } ?>
								<li>
									<div class="collapsible-header"><i class="large material-icons">add</i><a>New Chapter</a></div>
									<div class="collapsible-body">
										<?php new_chapter($book);?>
									</div>
								</li>
							</ul>
						</div>
					</li>
				<?php } ?>
				<li>
					<div class="collapsible-header modal-trigger" data-target="new"><i class="large material-icons">add</i><a>New Book</a></div>
				</li>
			</ul>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
	<?php edit_book_modals($books); ?>
	<?php order_book_modals($books); ?>
	<?php get_template_part('write/book','new'); ?>
<?php } else {
	get_template_part( 'template-parts/content', 'login' );
} ?>
<?php get_footer(); ?>
