<?php
/**
 * The template part for displaying single posts
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
$pinned = '';
$pin_text = '</i>Pin';
if (is_sticky()){
	$pinned = '<span style="color:var(--text-color);font-size: 15px;"><i style="margin-right:7px;" class="fas fa-thumbtack" aria-hidden="true"></i>Pinned</span>';
	$pin_text = 'Unpin';
}
?>
<!-- Dropdown Structure -->
<ul id='update-options-<?php the_ID(); ?>' class='dropdown-content'>
	<li><a class="stick-update" update_id="<?php the_ID(); ?>"><i class="fa fa-thumbtack" aria-hidden="true"></i><?php echo $pin_text; ?></a></li>
	<li><a class="delete-update" update_id="<?php the_ID(); ?>"><i class="fas fa-trash-alt" aria-hidden="true"></i>Delete</a></li>
</ul>
<article id="update-<?php the_ID(); ?>" >
	<?php if (get_current_user_id() == $post->post_author) { ?>
	<a class='dropdown-trigger right btn-hover btn-floating' data-target='update-options-<?php the_ID(); ?>'><i class="fas fa-angle-down"></i></a>
	<?php } ?>
	<header class="entry-header">
		<?php
		echo $pinned;
		?>
	</header><!-- .entry-header -->
	<div>
	<?php
		echo '<div><span style="display:block;height:15px;"><a style="color:var(--text-color) !important;font-weight:bold;" href="' .get_author_posts_url(get_post_field( 'post_author', $post->post_parent )) . '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->post_parent )) . '</a></span>';
		echo '<span style="display:block;" class="grey-text">' . get_the_time() . '</span></div>';
		echo '<pre style="line-height:1.4;">' . get_the_content() . '</pre>';
	?>
	</div>
</article><!-- #post-<?php the_ID(); ?> -->
