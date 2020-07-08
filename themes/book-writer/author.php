<?php
global $wp;

global $bundle;
$bundle = global_bundle('author');
$bundle->js('js/updates');
$bundle->js('js/views/search-options');
$bundle->js('js/views/search-content');
$bundle->enqueue();
get_header();
$author_base = rtrim(get_author_posts_url($author),'/') . '/';
?>

<section id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php get_template_part('author/header'); ?>
		<div class="author-content row">
			<div class="col s12 m6 l4">
				<?php if (get_the_author_meta('user_description',$author) != ''){ ?>
				<?php $bio_card = true; ?>
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
					$posts_card = true;
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
			<?php
			$class = ' m6 l8 ';
			if (! isset($posts_card) && ! isset($bio_card)){
				$class = "";
			}
			?>
			<div class="col s12 <?= $class; ?>">
				<?php
				global $book_query;
				$book_query = new book_query(array(
					'included'		=> array(
						'author'		=> array($author)
					),
					'per_page'		=> 3
				));
				if ( $book_query->has() || get_current_user_id() == $author) {
					?><div class="card author-books"><h2 class="type-title">Books</h2><?php
					$books_card = true;
					?>
					<book_collections hidden>
						<?= json_encode(collection::query_by_book(array_column($book_query->books,'ID'),'ID')); ?>
					</book_collections>
					<?php
					global $book;
					foreach ($book_query->books as $book) {
						get_template_part( 'template-parts/content' , 'search' );
					}
					if ($book_query->count > 3){
						?><div class="divider"></div><?php
						?><a href="<?php echo $author_base . 'books'; ?>" class="btn-hover more valign-wrapper"><i class="material-icons">expand_more</i>More Books</a><?php
					}
					if (get_current_user_id() === $author){
						?><a href="/dashboard/write" class="btn-hover more valign-wrapper"><i class="material-icons">add</i><span>Add book</span></a><?php
					}
					?></div><?php
				}
				?>
				<?php
				$collections = collection::query(array(
					'types'		=> array('Public','Favorites'),
					'authors'	=> array($author),
					'limit'		=> 4,
					'count'		=> array(
						'from'	=> 1
					),
					'orderby'	=> 'count',
					'order'		=> 'DESC'
				));
				if (! empty($collections)  || get_current_user_id() == $author) {
					?><div class="card author-collections"><h2 class="type-title">Collections</h2><?php
					?>
					<?php
					foreach ($collections as $key => $collection) {
						global $collection;
						get_template_part( 'collections/content' );
						if ($key === 2){
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
