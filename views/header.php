<?php
if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}
$landing = new _landing();
$app->landing_id = $landing_id;
$landing_key = $landing->encrypt();

//Nonce
$nonce = sha1(bin2hex(random_bytes(14)) . time());
if (! isset($_SESSION['nonce']) || ! is_array($_SESSION['nonce'])){
	$_SESSION['nonce'] = [];
}
$_SESSION['nonce'][] = $nonce;
$is_user_logged_in = is_user_logged_in();
$current_user = user::get( get_current_user_id() );
?>
<!DOCTYPE html>
<html lang="en" >
    <head>
        <meta charset="UTF-8"></meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $app->header_options['title']; ?></title>
		<?php
		if (isset($app->header_options['description'])){
			?><meta name="Description" content="<?= $app->header_options['description']; ?>"><?php
		}
		?>
		<!-- Preconnections -->
		<link rel="dns-prefetch" href="https://www.gstatic.com">
		<link rel="dns-prefetch" href="https://fonts.googleapis.com">
		<link rel="dns-prefetch" href="https://static.fanfiction.online">

		<!-- Load Font -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap">

		<!-- Manifest -->
		<link rel="manifest" href="/content/manifest/manifest.webmanifest" />
		<script>
		if ('serviceWorker' in navigator) {
			navigator.serviceWorker.register('/sw.js');
		}
		</script>
		<!-- Images -->
		<link rel="icon" type="image/png" href="<?= STATIC_URL(); ?>images/logos/16.png" sizes="16x16">
		<link rel="icon" type="image/png" href="<?= STATIC_URL(); ?>images/logos/32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="<?= STATIC_URL(); ?>images/logos/96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="<?= STATIC_URL(); ?>images/logos/128.png" sizes="128x128">
		<link rel="icon" type="image/png" href="<?= STATIC_URL(); ?>images/logos/192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="<?= STATIC_URL(); ?>images/logos/512.png" sizes="512x512">
		<link rel="apple-touch-icon" href="<?= STATIC_URL(); ?>images/logos/120.png">
		<link rel="apple-touch-icon" href="<?= STATIC_URL(); ?>images/logos/152.png" sizes="152x152">
		<link rel="apple-touch-icon" href="<?= STATIC_URL(); ?>images/logos/180.png" sizes="180x180">
		<!-- Meta -->
		<meta name="apple-mobile-web-app-status-bar" content="#007ACC">
		<meta name="theme-color" content="#007ACC">
		<meta name="color-scheme" content="dark light">

		<!-- Verifications -->
		<meta name="google-signin-client_id" content="<?= GOOGLE_CLIENT_ID ?>.apps.googleusercontent.com">
		
		<?php if (!DEV() && ($app->dont_load_analytics ?? false) !== true) { ?>
		<script async src="https://www.googletagmanager.com/gtag/js?id=G-WY1MXBDBJ1"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-WY1MXBDBJ1');
		</script>
		<?php } ?>
    </head>
    <body>
		<loading-shadow>
			<div class="dot-loader"></div>
			<div class="dot-loader dot-loader--2"></div>
			<div class="dot-loader dot-loader--3"></div>
		</loading-shadow>
		<style>loading-shadow.hide{opacity: 0;}loading-shadow{transition: opacity .3s;-webkit-box-flex:0;-ms-flex:0 0 25%;flex:0 0 25%;margin:0;position:relative;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-box-align:center;-ms-flex-align:center;align-items:center;overflow:hidden}loading-shadow{position:fixed;top:0;left:0;height:100vh;width:100vw;z-index:1000;background-color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#323335" : "#fdfdfd"?>}.dot-loader{height:20px;width:20px;border-radius:50%;background-color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#007ACC" : "#007ACC"?>;position:relative;-webkit-animation:1.2s grow ease-in-out infinite;animation:1.2s grow ease-in-out infinite}.dot-loader--2{-webkit-animation:1.2s grow ease-in-out infinite .15555s;animation:1.2s grow ease-in-out infinite .15555s;margin:0 20px}.dot-loader--3{-webkit-animation:1.2s grow ease-in-out infinite .3s;animation:1.2s grow ease-in-out infinite .3s}@-webkit-keyframes grow{0%,100%,40%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes grow{0%,100%,40%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}</style>

        <nonce hidden value="<?= $nonce; ?>"></nonce>
	    <placeholder_data hidden value="<?= $landing_key; ?>"></placeholder_data>
	    <logged_in hidden value="<?= $is_user_logged_in ? 'true' : 'false'; ?>"></logged_in>
		<dark-mode onclick="window.expose.themes_switch();"></dark-mode>
        <header>
			<a class="<?= $app->request === '/' ? 'active' : '' ?> logo waves-effect" href="/">
				<?php include(MAIN_DIR . '/content/logo.svg'); ?>
    		</a>
			<nav>
				<a class="<?= $app->request === '/read' ? 'active' : '' ?> waves-effect" href="/read">Read</a>
				<a class="<?= $app->request === '/my-stories' ? 'active' : '' ?> waves-effect" href="/my-stories">Write</a>
				<a class="<?= $app->request === '/collections' ? 'active' : '' ?> waves-effect" href="/collections">Collections</a>
				<?php if ($is_user_logged_in) { ?>
					<drop tabindex="0" class="<?= $app->request === '/@' . $current_user->user_login ? 'active' : '' ?> dropdown waves-effect" >
						Me
						<dropdown class="right">
							<a tabindex="0" class="notifications">Notifications</a>
							<a tabindex="0" href="/inbox">Inbox</a>
							<a tabindex="0" href="/@<?= $current_user->user_login; ?>">Profile</a>
							<a tabindex="0" href="/@<?= $current_user->user_login; ?>/settings">Settings</a>
							<a tabindex="0" href="/logout">Logout</a>
						</dropdown>
					</drop>
				<?php } else { ?>
					<a class="waves-effect" onclick="window.expose.prompt_login();">Login</a>
				<?php } ?>
			</nav>
		</header>
		<main>