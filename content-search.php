<?php
/**
 * The template part for displaying results in search pages
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
record_impressions();
?>
	
<article id="book-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header>
        <?php
        if (in_array($post->ID,get_stats_of('user_hidden',get_current_user_id()))){
            $class_of_eye = 'fa-eye-slash';
        }
        else{
            $class_of_eye = 'fa-eye';
        }
		if (is_user_logged_in()){
			$eye = '<a onclick="hide_book(' . $post->ID . ')" style="margin-left:10px;" class="btn-hover btn-floating"><i class="icon-hide far ' . $class_of_eye . '"></i></a>';
		}
		else{
			$eye = '';
		}
        ?>
		<div style="float:right;"> <?php echo '<a href="'. get_permalink(get_chapters_query()->posts[0]->ID) .'"><u>Start Reading -></u></a>'?> </div>
		<?php
		echo '<h2 class="entry-title" style="font-weight:500;display: inline-block;margin-bottom:0em;"><a href="' . get_permalink() . '" rel="bookmark">';
        if( $wp_query->query_vars['s'] != ''){
            $title = $post->post_title;
            foreach($wp_query->query_vars['search_terms'] as $term){
				$title = preg_replace('/(' . $term . ')+/i','<mark>$1</mark>',$title);
            }
            echo $title;
        }
        else{
            echo $post->post_title;
        }
		echo '</a></h2>' . $eye;
		?>
		<?php  // ?> 
		<?php echo '<h5 style="margin-bottom:1.05em;">by <a href="' .get_author_posts_url(get_post_field( 'post_author', $post->post_parent )). '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->post_parent )) . '</a></h5>'; ?>
	</header><!-- .entry-header -->
		
	<div class="search-summary">
		<?php if (preg_replace('/\s+/', '',$post->post_excerpt) != ''){
		    ?><p><?php
            if( $wp_query->query_vars['s'] != ''){
                $excerpt = $post->post_excerpt;
                foreach($wp_query->query_vars['search_terms'] as $term){
					$excerpt = preg_replace('/(' . $term . ')+/i','<mark>$1</mark>',$excerpt);
                }
                echo $excerpt;
            }
            else{
                echo $post->post_excerpt;
            }
            ?></p><?php
		} ?>
	<?php get_template_part( 'template-parts/header', 'taxonomy' ); ?>
	</div>
	<?php if ( 'book' === get_post_type() ) : ?>

		<footer>
			<?php
				//edit_post_link(
					//sprintf(
						/* translators: %s: Name of current post */
						//__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'twentysixteen' ),
						//get_the_title()
					//),
					//'<span class="edit-link">',
					//'</span>'
				//);
			?>
		</footer><!-- .entry-footer -->


	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->