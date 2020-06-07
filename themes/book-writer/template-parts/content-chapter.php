<?php

global $js_bundle;
$js_bundle = global_bundle('chapter');
$js_bundle->add('chapter');
$js_bundle->enqueue();

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
<style>
:root {
	--acs-font-size: 16;
	--acs-margin: 0;
	--acs-line-height: 7;
	--acs-p-height: 3;
}
.acs-main{
	margin: 0 calc(var(--acs-margin) * 10px) !important;
}
.acs-content{
	font-size: calc( var(--acs-font-size) * 1px ) !important;
	line-height: calc( var(--acs-line-height) * 0.25) !important;
}
.acs-content p{
	margin: 0 !important;
	margin-bottom: calc(var(--acs-p-height) * 7px) !important;
}
.acs-btn{
	font-size:20px !important;
}
.acs-icon{
	margin: 0 10px !important;
	font-size:24px !important;
	user-select: none;
}
.acs-icon.width-icon{
	transform: rotate(90deg);
}
</style>
<main id="main" class="site-main" role="main" style="margin: 0 5px;">
		<input type="hidden" id="chapter_id" value="<?php echo $post->ID; ?>">
		<header>
			<div>
				<?php
				echo '<h5> <a href="' .get_permalink($post->post_parent). '">' . get_the_title($post->post_parent) . '</a></h5>';
				echo '<h6>by ' . author_href($post->post_parent) . '</h6>';
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