<?php
/**
 * The template part for displaying an Author biography
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>

<div class="author-info">

	<div class="author-description">
		<h2 class="author-title">
			<span class="author-heading">
				<?php _e( 'Author:', 'twentysixteen' ); ?>
			</span>
			<?php
				echo '<a href="' .get_author_posts_url(get_post_field( 'post_author', $post->ID )). '">' . get_the_author() . '</a>';
			?>
		</h2>

		<p class="author-bio">
			<?php the_author_meta( 'description' ); ?>
		</p><!-- .author-bio -->
	</div><!-- .author-description -->
</div><!-- .author-info -->
