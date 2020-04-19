<?php
wp_enqueue_script('chapter', get_stylesheet_directory_uri() .'/js/chapter.js', array('jquery'), null, true);
record_landing(); 
?>
<div id="define" style="box-shadow:0 24px 38px 3px var(--text-color), 0 35px 46px 8px rgba(0,0,0,.32), 0 11px 15px -7px rgba(0,0,0,.2);display:none;margin:0;width: 100%;height: fit-content;position: fixed;bottom: 0;padding: 10px;left: 0;border-radius: 5px 5px 0px 0px;color: var(--text-color);background-color: var(--background-color);z-index: 1000;" class="row">
	<a data-target="define-full" class="modal-trigger btn-hover btn-floating right"><i class="fas fa-angle-up"></i></a>
	<div class="word"></div>
	<div class="first_meaning" style="color:var(--secondary-color);font-weight: 100;"></div>
	<div id="define-full" class="modal">
		<div class="modal-content">
			<h3 class="word row"></h3>
			<div class="all-meanings"></div>
		</div>
	</div>
</div>
<div id="primary" class="content-area">
<main id="main" class="site-main" role="main">
			<input type="hidden" id="chapter_id" value="<?php echo $post->ID; ?>">
			<header class="mobile-margin">
				<div>
					
		            <?php
					echo '<h5> <a href="' .get_permalink($post->post_parent). '">' . get_the_title($post->post_parent) . '</a></h5>';
					echo '<h6>by <a href="' .get_author_posts_url(get_post_field( 'post_author', $post->post_parent )). '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->post_parent )) . '</a></h6>';
				?>
				</div>
				<div style="float:right;">
						<button data-target="chapters-index" class="modal-trigger waves-effect waves-light btn">Index</button>
				</div>
		</header><!-- .page-header -->
	<?php
	// Start the loop.
		the_post();
	
		
		// Include the single post content template.
		get_template_part( 'template-parts/chapter', 'single' );
		chapter_navigator();
		?><!--<a href="whatsapp://send?text=dws">dsds</a><div class="right"><i style="padding-right:15px;" class="fa-2x btn-favorite far fa-share-square"></i><i style="padding-right:15px;"data-position="bottom" data-tooltip="Like" class="tooltipped fa-2x btn-favorite far fa-thumbs-up"></i></div>--><?php
		global $withcomments;
		$withcomments = 1;
		if (comments_open()){
			?><div id="comments-wrapper"><?php
			comments_template();
			?></div><?php
		}
		get_template_part( 'template-parts/content', 'accessibility' );
		get_template_part( 'template-parts/content', 'searchbook' );

	?>

</main><!-- .site-main -->
</div><!-- .content-area -->
  <!-- Modal Structure -->
  <div id="chapters-index" class="modal">
    <div class="modal-content">
		<?php get_template_part('template-parts/content','index'); ?>
    </div>
  </div>