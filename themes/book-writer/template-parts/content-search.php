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
<style>
	.entry-title{
		font-size:;
	}
</style>	
<article id="book-<?php the_ID(); ?>" class="mainsearch-item" <?php //post_class(); ?>>
	  <a class='dropdown-trigger book-options btn-hover btn-floating' data-target='book-options-<?php the_ID(); ?>'><i class="fas fa-angle-down"></i></a>
	  <!-- Dropdown Structure -->
	  <ul id='book-options-<?php the_ID(); ?>' class='dropdown-content'>
	    <li><a class="book-share"><i class="fas fa-share-alt"></i>Share</a></li>
	    <li><a class="book-favorite"><i class="fas fa-heart"></i><?php if (in_array(get_current_user_id(),get_stats_of('book_fav',$post->ID))){echo 'Remove';}else {echo 'Favorite';} ?></a></li>
	    <li><a  class="book-hide"><i class="fas fa-eye-slash"></i>Hide</a></li>
	    <li><a <?php if (! is_user_logged_in()){echo ' disabled ';} ?> class="book-collections"><i class="fas fa-list-ul"></i>Collections</a></li>

	  </ul>
	<div class="home-desc">
	<header>
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
		echo '</a></h2>';
		?>
		<?php 
		if ($post->post_author == 37 && metadata_exists('post',$post->ID,'source_author')){
			echo '<h5 class="entry-author" style="margin-bottom:1.05em;">by ' . get_post_meta($post->ID,'source_author',true) .  ' (<a href="' .get_author_posts_url(get_post_field( 'post_author', $post->post_parent )) . '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->post_parent )) . '</a>)</h5>';
		}
		else{
			echo '<h5 class="entry-author" style="margin-bottom:1.05em;">by <a href="' .get_author_posts_url(get_post_field( 'post_author', $post->post_parent )) . '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->post_parent )) . '</a></h5>';
		}
		?>
	</header><!-- .entry-header -->
		
	<div class="search-summary">
		<p>
		<?php if (preg_replace('/\s+/', '',$post->post_excerpt) != ''){
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
		} ?>
		</p>
	</div>
	<?php echo '<h4 class="update-time"> Updated ' . get_the_time() . '</h4>'; ?>
	</div>
	<?php get_template_part( 'template-parts/header', 'taxonomy' ); ?>
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
    <!-- Modal Structure -->
    <div id="collections-<?php the_ID(); ?>" class="modal bottom-sheet">
    	<div class="modal-content">
    	 <?php
    	 $collections_of_book = wp_get_post_terms($post->ID,'collection',array('fields'=>'ids'));
    	 $collections = get_terms(array(
            'meta_key' => 'author',
            'meta_value' => get_current_user_id(),
            'taxonomy' => 'collection',
            'hide_empty' => false,
        ));
        ?>
        <table><tbody>
			<tr>
				<td>Favorites <label>(Private)</label></td>
				<td class="right-align">
					<div class="switch"><label>
						<input class="book-favorite" <?php if (in_array(get_current_user_id(),get_stats_of('book_fav',$post->ID))){echo 'checked';} ?> id="d-switch-favorite" type="checkbox">
						<span class="lever"></span>
						</label></div>
				</td>
			</tr>
			<tr>
				<td>Hidden <label>(Private)</label></td>
				<td class="right-align">
					<div class="switch"><label>
						<input class="book-hide" <?php if (in_array($post->ID,get_stats_of('user_hidden',get_current_user_id()))){echo 'checked';} ?> id="d-switch-hidden" type="checkbox">
						<span class="lever"></span>
						</label></div>
				</td>
			</tr>
			<?php foreach($collections as $collection) { ?>
                <tr>
                    <td><?php echo $collection->name . ' <label>(' . get_term_meta($collection->term_id,'public_collection',true) . ')</label>'; ?></td>
                    <td class="right-align">
                        <div class="switch"><label>
                            <input class="save-collection" <?php if (in_array($collection->term_id,$collections_of_book)){echo 'checked';} ?> collection_id="<?php echo $collection->term_id; ?>" type="checkbox">
                            <span class="lever"></span>
                        </label></div>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td><a href="/dashboard/collections">Create Collection</a></td>
                <td></td>
            </tr>            
        </tbody></table>
    	</div>
    </div>
</article><!-- #post-<?php the_ID(); ?> -->