<?php
/**
 * Template Name: Collection
 *
 * Search page
 *
 */
$collection_id = get_queried_object()->term_id;
$collection_author = get_userdata(get_term_meta($collection_id,'author',true));
if (get_term_meta($collection_id,'public_collection',true) == 'Private' && get_current_user_id() != $collection_author->ID){
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
	get_header();
	get_template_part('404');
	get_footer();
	exit();
}
get_header();
if (! isset($_SESSION)){
	session_start();
}

$collection = get_term($collection_id);
$collection_query = array(
	'relation' => 'AND',
    array(
    	'taxonomy'         => 'collection',
    	'terms'            => $collection_id,
    	'field'            => 'term_id',
    	'operator'         => 'AND',	
    )
);
$max_count = new WP_Query( array(
    'tax_query'      => $collection_query,
	'post_type'      => array( 'book' ),
	'meta_key'       => 'word-count',
	'orderby'        => 'meta_value_num',
	'posts_per_page' => 1,
	'order'          => 'DESC',
	'cache_results'  => true,
	'post__not_in'	 => get_stats_of('user_hidden',get_current_user_id()),
) );
$max_count = get_post_meta( $max_count->posts[0]->ID, 'word-count', true );
?>
<span id="max_count" style="display:none;"><?php echo $max_count; ?></span>
<span id="search_extras" style="display:none;">collection_<?php echo $collection_id; ?></span>
<?php
$args = create_args_from_url();
$args['tax_query'][] = array(
	'taxonomy'         => 'collection',
	'terms'            => $collection_id,
	'field'            => 'term_id',
	'operator'         => 'AND',
);
$_SESSION["search_args"] = $args;
$default_query = new WP_Query( $args );
$original_query = $wp_query;
$wp_query = null;
$wp_query = $default_query;
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php echo '<div class="row"><h2 class="center-align col s12" >' . $collection->name .  ' (' . $collection->count . ' Books)' . '</h2><h4 class="center-align col s12" >by <a href="' . get_author_posts_url($collection_author->ID) . '">' . $collection_author->display_name . '</a></h4></div>'; ?>
		<button style="margin-bottom:50px;" class="mobile-margin waves-effect waves-light modal-trigger btn-small right" href="#search-settings">Filters</button>
		<div class="row"><div id="box" class="col s12">
			<?php
			if (have_posts()){
				// Start the loop.
				while (have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'search' );
					
					// End the loop.
				endwhile;
				
			}
			else {
				get_template_part( 'template-parts/content', 'noresult' );
			}
			?>
		</div></div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_template_part( 'template-parts/content', 'bookpaginate' ); ?>

<?php
//Reset Data
//This is because we dont want our meddling of the $wp-query to affect the whole site
$wp_query = null;
$wp_query = $original_query;

wp_reset_postdata();
get_template_part( 'template-parts/content', 'searchmodal' );

get_footer();