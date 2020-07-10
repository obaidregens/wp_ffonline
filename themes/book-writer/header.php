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
<dark-mode onclick="themes.switch();"></dark-mode>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<!--Let browser know website is optimized for mobile-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
</head>
<body>
	<span style="display:none;" id="nonce"><?= $nonce; ?></span>
	<span style="display:none;" id="placeholder_data"><?= $landing_key; ?></span>
	<placeholder_data hidden><?= $landing_key; ?></placeholder_data>
	<logged_in hidden value="<?= is_user_logged_in() ? 'true' : 'false'; ?>"></logged_in>
<div id="page" class="site">
	<div class="site-inner">
		<header>
			<a class="logo" href="/">
				<?php include(explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/images/logo.svg'); ?>
    		</a>
			<nav>
				<a href="/">Read</a>
				<a href="/collections">Collections</a>
				<a href="/dashboard">Dashboard</a>
			</nav>
		</header>
		
		<div id="content" class="site-content">
