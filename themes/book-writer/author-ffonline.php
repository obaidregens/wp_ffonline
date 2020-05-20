<?php
/**
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

global $wp;
get_header(); ?>

<section id="primary" class="content-area margin">
	<main id="main" class="site-main" role="main">
	<?php
	echo '<h1 class="page-title section">Announcements</h1>' ;
	$default_query = new WP_Query( array(
		'post_type' => 'post',
		'orderby'	=> 'modified',
	));
	$original_query = $wp_query;
	$wp_query = null;
	$wp_query = $default_query;

	if ( have_posts() ) :
	// Start the Loop.
	while ( have_posts() ) :
	the_post();

	?><div class="section"></div><?php
	get_template_part( 'template-parts/content', 'announcement' );

	endwhile;
	endif;
	
?>
		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
<?php

?>