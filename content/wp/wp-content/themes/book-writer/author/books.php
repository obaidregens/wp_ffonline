<?php
/**
 * Template Name: Collection
 *
 * Search page
 *
 */
get_header();
get_template_part('author/header');
$author_obj = get_user_by('ID',$author);
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php get_template_part('template-parts/create','search'); ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
get_footer();