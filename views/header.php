<?php
if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}
$landing = new _landing();
$landing_key = $landing->encrypt();

//Nonce
$nonce = sha1(bin2hex(random_bytes(14)) . time());
if (! isset($_SESSION['nonce']) || ! is_array($_SESSION['nonce'])){
	$_SESSION['nonce'] = [];
}
$_SESSION['nonce'][] = $nonce;
$is_user_logged_in = is_user_logged_in();
$current_user = get_userdata( get_current_user_id() );
?>
<!DOCTYPE html>
<html lang="en" <?php if ($_COOKIE['theme'] ?? 'light' === 'dark'){ ?>class="force-dark"<?php } ?> >
    <head>
		<style>html.force-dark {background-color: #121212;}</style>
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
		<link rel="icon" type="image/png" href="/content/static/images/logos/16.png" sizes="16x16">
		<link rel="icon" type="image/png" href="/content/static/images/logos/32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="/content/static/images/logos/96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="/content/static/images/logos/128.png" sizes="128x128">
		<link rel="icon" type="image/png" href="/content/static/images/logos/192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="/content/static/images/logos/512.png" sizes="512x512">
		<link rel="apple-touch-icon" href="/content/static/images/logos/120.png">
		<link rel="apple-touch-icon" href="/content/static/images/logos/152.png" sizes="152x152">
		<link rel="apple-touch-icon" href="/content/static/images/logos/180.png" sizes="180x180">
		<!-- Meta -->
		<meta name="apple-mobile-web-app-status-bar" content="#007ACC">
		<meta name="theme-color" content="#007ACC">
    </head>
    <body>
        <nonce hidden><?= $nonce; ?></nonce>
	    <placeholder_data hidden><?= $landing_key; ?></placeholder_data>
	    <logged_in hidden value="<?= $is_user_logged_in ? 'true' : 'false'; ?>"></logged_in>
		<dark-mode onclick="themes.switch();"></dark-mode>
        <header>
			<a class="<?= $app->request === '/' ? 'active' : '' ?> logo waves-effect" href="/">
				<?php include(MAIN_DIR . '/content/static/images/logo.svg'); ?>
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
					<a class="waves-effect" onclick="prompt_login();">Login</a>
				<?php } ?>
			</nav>
		</header>
		<main>