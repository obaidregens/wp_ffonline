<?php
/**
 * The template part for displaying results in search pages
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
global $collection;
$collection_author = get_userdata(get_term_meta($collection->term_id,'author',true));
$collection_time = get_term_meta($collection->term_id,'time_created',true);
?>
	
<article id="collection-<?php echo $collection->ID; ?>">
	<header>

		<h2 class="entry-title" style="display: inline-block;margin-bottom:0em;"><a href="<?php echo get_term_link($collection->term_id); ?>"><?php echo $collection->name; ?></a></h2>
		
		<?php echo '<h5 style="margin-bottom:1.05em;">by <a href="' .  get_author_posts_url($collection_author->ID) . '">' . $collection_author->display_name .  '</a></h5>'; ?>
	</header><!-- .entry-header -->
		
	<div class="search-summary">
		<?php echo '<p>' . $collection->description . '</p>'; ?>
		<div><strong>Books: </strong><?php echo $collection->count; ?> <strong>Created: </strong><?php echo human_time_diff($collection_time) . ' ago'; ?></div>
	</div>
</article>