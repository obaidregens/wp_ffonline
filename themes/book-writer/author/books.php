<?php
global $wp;
get_header();
$default_query = new WP_Query( array(
		'post_type' => 'book',
		'author' => $author,
	));
	$original_query = $wp_query;
	$wp_query = null;
	$wp_query = $default_query;

	if ( have_posts() ) :
	// Start the Loop.
	while ( have_posts() ) :
		the_post();

		?><div class="section"></div><?php
		get_template_part( 'template-parts/content', 'search' );

	endwhile;

	the_posts_pagination(
		array(
			'prev_text'          => __( 'Previous page', 'twentysixteen' ),
			'next_text'          => __( 'Next page', 'twentysixteen' ),
			'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentysixteen' ) . ' </span>',
		));
	// If no content, include the "No posts found" template.
else :
?>
    <div class="section"></div>
<article class="margin">
		<h1 style="padding-bottom: 12px;" class="page-title"><?php _e( 'No Books', 'twentysixteen' ); ?></h1>
</article>
<?php endif;
//Reset Data
//This is because we dont want our meddling of the $wp-query to affect the whole site

$wp_query = null;
$wp_query = $original_query;
wp_reset_postdata();

?>
<?php get_footer(); ?>
