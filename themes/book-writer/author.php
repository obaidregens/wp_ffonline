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

global $vfs;
$vfs = vfs();
log_stats('view','author',$author,$vfs);
global $js_bundle;
$js_bundle = global_bundle('author');
$js_bundle->add('updates');
$js_bundle->add('book-options');
$js_bundle->enqueue();
get_header();
$author_base = get_author_posts_url($author) . '/';
?>

<section id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php get_template_part('author/header'); ?>
		<div class="author-content row">
			<div class="col s12 m6 l4">
				<?php if (get_the_author_meta('user_description',$author) != ''){ ?>
				<div class="card author-bio">
					<pre><?php echo get_the_author_meta('user_description',$author); ?></pre>
				</div>
				<?php } ?>
				<?php
				$posts = new WP_Query(array(
					'post_type'      => array( 'post' ),
					'orderby'        => 'modified',
					'posts_per_page' => 2,
					'order'          => 'DESC',
					'author__in' 	 => array( $author )
				));
				$original_query = $wp_query;
				$wp_query = null;
				$wp_query = $posts;
				if ( have_posts()  || get_current_user_id() == $author) :
					?><div class="card author-updates"><?php
					while (have_posts()) :
						the_post();
						get_template_part( 'template-parts/content', 'update' );
					endwhile;
					if ($wp_query->found_posts > 2){
						?><div class="divider"></div><?php
						?><a href="<?php echo $author_base . 'updates'; ?>" class="btn-hover more valign-wrapper"><i class="material-icons">expand_more</i>Older Updates</a><?php
					}
					if (get_current_user_id() == $author){
						?><a class="new-update btn-hover more valign-wrapper"><i class="material-icons">add</i><span>Add Update</span></a><?php
					}
					?></div><?php
				endif;
				//Reset Data
				//This is because we dont want our meddling of the $wp-query to affect the whole site
				$wp_query = null;
				$wp_query = $original_query;
				wp_reset_postdata();
				?>
			</div>
			<div class="col s12 m6 l8">
				<?php
				$posts = array(
					'post_type'      => array( 'book' ),
					'orderby'        => 'modified',
					'author__in'	 => $author,
					'posts_per_page' => 3,
					'order'          => 'DESC',
				);
				//if (get_current_user_id() == $author  && $_GET['preview'] == 'true'){
				//	$posts['post_status'] = array('publish','draft');
				//}
				$posts = new WP_Query($posts);
				$original_query = $wp_query;
				$wp_query = null;
				$wp_query = $posts;
				if ( have_posts()   || get_current_user_id() == $author) :
					?><div class="card author-books"><h2 class="type-title">Books</h2><?php
					while (have_posts() ) :
						the_post();
						?><div style="position: relative;"><div <?php if ($post->post_status == 'draft'){ ?>class="preview"<?php } ?> ><?php
						get_template_part( 'template-parts/content', 'search' );
						?></div></div><?php
					endwhile;
					if ($wp_query->found_posts > 3){
						?><div class="divider"></div><?php
						?><a href="<?php echo $author_base . 'books'; ?>" class="btn-hover more valign-wrapper"><i class="material-icons">expand_more</i>More Books</a><?php
					}
					if (get_current_user_id() == $author){
						?><a href="/dashboard/write" class="btn-hover more valign-wrapper"><i class="material-icons">add</i><span>Add book</span></a><?php
					}
					?></div><?php
				endif;
				//Reset Data
				//This is because we dont want our meddling of the $wp-query to affect the whole site
				$wp_query = null;
				$wp_query = $original_query;
				wp_reset_postdata();
				?>
				<?php
				$meta_query = array(
					array(
						'key'     => 'public_collection',
						'value'   => 'Public',
					),
				);
				$collections = get_terms(array(
				    'meta_key' => 'author',
				    'meta_value' => $author,
				    'meta_query' => $meta_query,
				    'taxonomy' => 'collection',
				    'hide_empty' => true,
				));
				if (get_current_user_id() == $author){
					//$posts['post_status'] = array('publish','draft');
				}
				if (! empty($collections)   || get_current_user_id() == $author) {
					?><div class="card author-collections"><h2 class="type-title">Collections</h2><?php
					?>
					<article id="collection-favorites">
						<header>
							<h2 class="entry-title" style="display: inline-block;margin-bottom:0em;"><a href="<?php echo $author_base . 'favorites'; ?>">Favorites</a></h2>
							
							<?php echo '<h5 style="margin-bottom:1.05em;">by <a href="' .  $author_base . '">' . get_the_author_meta('display_name',$author) .  '</a></h5>'; ?>
						</header><!-- .entry-header -->
							
						<div class="search-summary">
							<div><strong>Books: </strong><?php echo count(get_stats_of('user_fav',$author)); ?></div>
						</div>
					</article>
					<?php
					foreach ($collections as $key => $collection) {
						global $collection;
						get_template_part( 'template-parts/content', 'collection' );
						if ($key == 2){
							break;
						}
					}
					if (count($collections) > 3){
						?><div class="divider"></div><?php
						?><a href="<?php echo $author_base . 'collections'; ?>" class="btn-hover more valign-wrapper"><i class="material-icons">expand_more</i>More collections</a><?php
					}
					if (get_current_user_id() == $author){
						?><a href="/dashboard/collection" class="btn-hover more valign-wrapper"><i class="material-icons">add</i><span>Add collection</span></a><?php
					}
					?></div><?php
				}
				?>
			</div>
		</div>

	</main><!-- .site-main -->
</section><!-- .content-area -->
<!-- Modal Structure -->
<div id="edit-update" class="modal">
	<form id="update_form">
		<div class="modal-content">
			<h4 class="row" id="update_header">Add Update</h4>
			<input type="hidden" id="update_id" value="new">
			<div class="row">
				<div class="input-field col s12">
					<textarea required name="update" id="update" class="materialize-textarea"></textarea>
					<label for="update">Update</label>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="waves-effect btn">Save</button>
		</div>
	</form>
</div>
<?php get_footer(); ?>
<?php
?>
