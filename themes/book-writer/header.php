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
$landing = new _landing();
$landing_key = $landing->encrypt();

//Nonce
$nonce = bin2hex(random_bytes(14));
if (! isset($_SESSION['nonce']) || ! is_array($_SESSION['nonce'])){
	$_SESSION['nonce'] = array($nonce);
}
else{
	$_SESSION['nonce'][] = $nonce;
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
	<span style="display:none;" id="nonce"><?= $nonce; ?></span>
	<span style="display:none;" id="placeholder_data"><?= $landing_key; ?></span>
	<ul style="user-select: none;-moz-user-select: none;-khtml-user-select: none;-webkit-user-select: none;-o-user-select: none;width:55%;" id="notification-sidenav" class="sidenav">
	</ul>
<div id="page" class="site">
	<div class="site-inner">
		<header id="masthead" class="site-header" role="banner">
			<div class="site-header-main">
				<nav class="nav-wrapper" style="width:100%">
					<div class="nav-logo">						
						<a href="/" ><?php include(explode('wp-content',__FILE__)[0] . 'wp-content/uploads/logo.svg'); ?></a>
					</div>
					<div class="nav-content">
						<li><a target="_self" href="/">Read</a></li>
						<li><a target="_self" href="/collections">Collections</a></li>
						<li><a target="_self" href="/dashboard">Dashboard</a></li>
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
