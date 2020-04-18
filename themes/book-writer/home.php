<?php
/**
 * Template Name: Home
 *
 *
 */
get_header();
?>
            
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
    	<div class="row">
			<div class="col s12">
        		<?php
                 // the query
                $default_query = new WP_Query( array(
                    'post_type' => 'book',
                	'orderby'	=> 'modified',
                	'posts_per_page' => 5,
                ));
                $original_query = $wp_query;
                $wp_query = null;
                $wp_query = $default_query;
        		// Start the loop.
        		while ( have_posts() ) :
        			the_post();
        
        			// Include the page content template.
        			get_template_part( 'template-parts/content', 'search' );
        
        
        			// End of the loop.
        		endwhile;
                //Reset Data
                //This is because we dont want our meddling of the $wp-query to affect the whole site
                $wp_query = null;
                $wp_query = $original_query;
                wp_reset_postdata();
        		?>			    
			</div>
		</div>
		<div class="row">
			<div class="col s12">
        		<?php
                 // the query
                $default_query = new WP_Query( array(
                    'post_type' => 'post',
                	'orderby'	=> 'modified',
                	'posts_per_page' => 5,
                ));
                $original_query = $wp_query;
                $wp_query = null;
                $wp_query = $default_query;
        		// Start the loop.
        		while ( have_posts() ) :
        			the_post();
        			?>
                        <div class="card margin">
                            <div class="card-content z-depth-5">
                                <span class="card-title grey-text text-darken-4"><?php the_title(); ?></span>
                                <p><?php the_content(); ?></p>
                            </div>
                        </div>
                    <?php
        			// End of the loop.
        		endwhile;
                //Reset Data
                //This is because we dont want our meddling of the $wp-query to affect the whole site
                $wp_query = null;
                $wp_query = $original_query;
                wp_reset_postdata();
        		?>			    
			</div>
		</div>
	</main><!-- .site-main -->

	<?php get_sidebar( 'content-bottom' ); ?>

</div><!-- .content-area -->
<?php

get_sidebar();
get_footer();