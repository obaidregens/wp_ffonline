<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
global $template;
$template_pages = str_replace("/home3/eshaatco/public_html/fic/wp-content/themes/book-writer/","",$template);
$no_login_modal = array('page-dashboard.php','page-login.php');
?>
		<!-- Modal Structure -->
		<?php if (is_user_logged_in() == false && in_array($template_pages,$no_login_modal) == false){ ?>
		<div id="login-modal" class="modal">
			<div class="modal-content">
				<?php get_template_part('template-parts/content','login'); ?>
			</div>
		</div>
		<?php } ?>
		</div><!-- .site-content -->

		<footer style="padding-right:0.75rem;padding-left:0.75rem;" id="colophon" class="site-footer" role="contentinfo">
			<?php if ( has_nav_menu( 'primary' ) && 1 == 0) : ?>
				<nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Footer Primary Menu', 'twentysixteen' ); ?>">
					<?php
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'menu_class'     => 'primary-menu',
							)
						);
					?>
				</nav><!-- .main-navigation -->
			<?php endif; ?>

			<?php if ( has_nav_menu( 'social' ) ) : ?>
				<nav class="social-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Footer Social Links Menu', 'twentysixteen' ); ?>">
					<?php
						wp_nav_menu(
							array(
								'theme_location' => 'social',
								'menu_class'     => 'social-links-menu',
								'depth'          => 1,
								'link_before'    => '<span class="screen-reader-text">',
								'link_after'     => '</span>',
							)
						);
					?>
				</nav><!-- .social-navigation -->
			<?php endif; ?>

			<div class="site-info">
				<?php
				if ( function_exists( 'the_privacy_policy_link' ) ) {
				    
					the_privacy_policy_link( '', '<span role="separator" aria-hidden="true"></span>' );
				}
				?>
				<a href="/author/ffonline">Announcements</a>
				<span role="separator" aria-hidden="true"></span>
				<a href="/contact">Contact</a>
			</div><!-- .site-info -->


			
		</footer><!-- .site-footer -->
	</div><!-- .site-inner -->

</div><!-- .site -->
<!-- Theme -->
<a class="btn-floating" id="triggerTheme" style="z-index:999999;position:absolute;top:-6px;left:-6px;background-color: var(--background-color);"><i style="color:var(--text-color) !important;" class="fas fa-adjust"></i></a>

<?php wp_footer(); ?>
<!-- Fonts -->
<!-- Font Awesome -->
<link rel="stylesheet" id="font-awesome-official-css" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" type="text/css" media="all" integrity="sha384-KA6wR/X5RY4zFAHpv/CnoG2UW1uogYfdnP67Uv7eULvTveboZJg0qUpmJZb5VqzN" crossorigin="anonymous">
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<div class="progress" style="display:none;margin:0;position: fixed;bottom: 0px;right: 0px;left: 0px;"><div class="indeterminate"></div></div>
</body>
</html>
