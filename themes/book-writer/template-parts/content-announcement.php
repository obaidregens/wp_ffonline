<?php
/**
 * The template part for displaying single posts
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header-c class="entry-header">
		<?php
		$pinned = '';
		if (is_sticky()){
			$pinned = '<span style="color:var(--text-color);font-size: 15px;"><i class="fa fa-thumb-tack" aria-hidden="true"></i> Pinned</span>';
		}
		
		the_title( '<h1 class="entry-title">', '  ' . $pinned . '</h1>'); ?>
	</header-c><!-- .entry-header -->



	<div class="margin">
		<?php
			the_content();
			?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
