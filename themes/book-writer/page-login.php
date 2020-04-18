<?php
/**
 * Template Name: Login
 *
 * Allow users to login.
 *
 */
?>
<?php
if (is_user_logged_in()){
	wp_redirect('/dashboard');
	exit();
}
else{
	get_header();
	get_template_part( 'template-parts/content', 'login' );
	get_sidebar();
	get_footer();
}