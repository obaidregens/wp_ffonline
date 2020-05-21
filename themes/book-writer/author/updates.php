<?php
/**
 * Template Name: Collection
 *
 * Search page
 *
 */
global $js_bundle;
$js_bundle = global_bundle('author_updates');
$js_bundle->add('updates');
$js_bundle->enqueue();
get_header();
$author_obj = get_user_by('ID',$author);
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		get_template_part('author/header');
		$posts = new WP_Query(array(
			'post_type'      => array( 'post' ),
			'orderby'        => 'modified',
			'posts_per_page' => -1,
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
			if ($wp_query->found_posts > 15){
				?><!--<div class="divider"></div><?php
				?><a class="btn-hover more valign-wrapper"><i class="material-icons">expand_more</i>Older Updates</a>--><?php
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
	</main><!-- .site-main -->
</div><!-- .content-area -->
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
<?php
get_footer();