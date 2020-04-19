<?php
/**
 * Template Name: Collection
 *
 * Search page
 *
 */
get_header();
$author_obj = get_user_by('ID',$author);
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php get_template_part('author/header'); ?>
		<div class="row">
			<div class="col s12">
				<select style="background-image:none;" onChange="window.location.href='?sortby=' + this.value">
					<option value="" disabled selected>Sort by</option>
					<option value="most-books">Most Books</option>
					<option value="least-books">Least Books</option>
					<option value="newest">Newest</option>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col s12 author-collections">
			<?php
			$meta_query = array(
				array(
					'key'     => 'public_collection',
					'value'   => 'Public',
				),
			);
			$args = array(
			    'meta_key' => 'author',
			    'meta_value' => $author,
			    'meta_query' => $meta_query,
			    'taxonomy' => 'collection',
			    'hide_empty' => true,
			);
			if (isset($_GET['sortby']) && $_GET['sortby']){
				if ($_GET['sortby'] == 'most-books'){
					$args['orderby'] = 'count';
					$args['order'] = 'DESC';
				}
				else if($_GET['sortby'] == 'least-books'){
					$args['orderby'] = 'count';
					$args['order'] = 'ASC';		
				}
				else if ($_GET['sortby'] == 'newest'){
					$args['meta_key'] = 'time_created';
					$args['orderby'] = 'meta_value_num';
					$args['order'] = 'DESC';		
				}
			}
			$collections = get_terms($args);
			foreach ($collections as $key => $collection) {
				global $collection;
				get_template_part( 'template-parts/content', 'collection' );
			}
			if (get_current_user_id() == $author){
				?><a href="/dashboard/collections" class="btn-hover more valign-wrapper"><i class="material-icons">add</i><span>Add collection</span></a><?php
			}
			?>
		</div>
	</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
get_footer();