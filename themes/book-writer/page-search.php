<?php
/**
 * Template Name: Search/Read
 *
 * Search page
 *
 */
// new c_user(array(
// 	'connection_user'	=> '12f'
// ));
get_header();
?>
<div>
	<main>
		<?php get_template_part('template-parts/create','search'); ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
	get_footer();
?>