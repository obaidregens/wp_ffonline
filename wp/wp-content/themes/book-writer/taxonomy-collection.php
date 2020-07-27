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

$collection = get_term($collection_id);
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php echo '<div class="row"><h2 class="center-align col s12" >' . $collection->name .  ' (' . $collection->count . ' Books)' . '</h2><h4 class="center-align col s12" >by <a href="' . get_author_posts_url($collection_author->ID) . '">' . $collection_author->display_name . '</a></h4></div>'; ?>
		<?php get_template_part('template-parts/create','search'); ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
get_footer();