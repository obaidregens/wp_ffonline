<?php  if (!empty(get_stats_of('user_fav',$author))) {
  $default_query = new WP_Query( array(
	  'post_type' => 'book',
	  'post__in' => get_stats_of('user_fav',$author),
  ));
  $original_query = $wp_query;
  $wp_query = null;
  $wp_query = $default_query;

  // Start the Loop.
  while ( have_posts() ) {
	  the_post();

?><div class="section"></div><?php
  get_template_part( 'template-parts/content', 'search' );

  }

  the_posts_pagination(
	  array(
		  'prev_text'          => __( 'Previous page', 'twentysixteen' ),
		  'next_text'          => __( 'Next page', 'twentysixteen' ),
		  'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentysixteen' ) . ' </span>',
	  ));
  // If no content, include the "No posts found" template.
  			  //Reset Data
//This is because we dont want our meddling of the $wp-query to affect the whole site

$wp_query = null;
$wp_query = $original_query;
wp_reset_postdata();
}
else {
?>
<div class="section"></div>
<article class="margin">
	<h1 style="padding-bottom: 12px;" class="page-title"><?php _e( 'No Favourites!', 'twentysixteen' ); ?></h1>
</article>
<?php
																								  }
																				
?>