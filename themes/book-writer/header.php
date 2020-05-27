<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}

global $vfs;
if (!isset($vfs)){
	$vfs = vfs();
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<!--Let browser know website is optimized for mobile-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body>
	<?php if (is_user_logged_in()){ ?>
	<ul style="user-select: none;-moz-user-select: none;-khtml-user-select: none;-webkit-user-select: none;-o-user-select: none;width:55%;" id="notification-sidenav" class="sidenav">
		<?php get_template_part('template-parts/header','notifications'); ?>
	</ul>
	<?php } ?>
<div id="page" class="site">
	<div class="site-inner">
		<header id="masthead" class="site-header" role="banner">
			<div class="site-header-main">
				<nav class="nav-wrapper" style="width:100%">
					<div class="nav-logo">						
						<a href="/" ><?php include(explode('wp-content',__FILE__)[0] . 'wp-content/uploads/logo.svg'); ?></a>
					</div>
					<div class="nav-content">
						<li><a <?php if ($pagename == 'search' || $pagename == ''){echo 'class="pagenow active"';} ?> target="_self" href="/">Read</a></li>
						<li><a <?php if ($pagename == 'collection'){echo 'class="pagenow active"';} ?>  target="_self" href="/collection">Collections</a></li>
						<li><a <?php if ($pagename == 'dashboard'){echo 'class="pagenow active"';} ?> target="_self" href="/dashboard">Dashboard</a></li>
					    <?php if (is_user_logged_in()) { ?>
						<li style="position:relative;">
							<a data-target="notification-sidenav" class="sidenav-trigger btn-hover btn-floating"><i class="fas fa-bell"></i></a>
							<span class="pulse notification-badge"></span></li>
	                    <?php } ?>
					</div>
				</nav>				
			</div><!-- .site-header-main -->
		</header><!-- .site-header -->

		<div id="content" class="site-content">
