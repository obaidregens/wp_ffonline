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
		
			<?php echo '<h1 class="page-title section">'.  get_the_author_meta('display_name',$author) . '<a href="/dashboard/?to=chat&id=' . $author . '" style="margin-left: 10px;" class="btn-floating btn-hover"><i class="fas fa-envelope"></i></a>' . '</h1>' ; ?>
			<div class="row">
				<div>
					<ul class="tabs">
						<li class="tab col s4" ><a href="#about">About <?php echo get_the_author_meta('display_name',$author) ?></a></li>
						<li class="tab col s4" ><a href="#books">Books</a></li>
						<li class="tab col s4" ><a href="#favorites">Favorites</a></li>
					</ul>
				</div>
				<div id="about" class="col s12">
					<div class="section "></div>
					<?php echo '<div class="margin taxonomy-description"><strong>About '. get_the_author_meta('display_name',$author) .'</strong><br>' . get_the_author_meta('description',$author) . '</div>'; ?>
				</div>
				<div id="favorites" class="col s12">
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
				</div>
				<div id="books" class="col s12">
				<?php
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
			</div>
			</div>
		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
<?php

?>
