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
<html>
    <head>
        <meta charset="UTF-8"></meta>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $app->header_options['title']; ?></title>
		<meta name="Description" content="<?= $app->header_options['description']; ?>">
		<link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
		<link rel="icon" href="/content/static/images/logo.svg"  type="image/svg+xml">
		<!-- Meta -->
    </head>
    <body>
        <nonce hidden><?= $nonce; ?></nonce>
	    <placeholder_data hidden><?= $landing_key; ?></placeholder_data>
	    <logged_in hidden value="<?= $is_user_logged_in ? 'true' : 'false'; ?>"></logged_in>
		<dark-mode onclick="themes.switch();"></dark-mode>
        <header>
			<a class="logo" href="/">
				<?php include('static/images/logo.svg'); ?>
    		</a>
			<nav>
				<a href="/read">Read</a>
				<a href="/my-stories">Write</a>
				<a href="/collections">Collections</a>
				<?php if ($is_user_logged_in) { ?>
					<drop tabindex="0" class="dropdown" >
						Me
						<dropdown class="right">
							<a href="/@<?= $current_user->user_login; ?>">Profile</a>
							<a href="/@<?= $current_user->user_login; ?>/settings">Settings</a>
							<a href="/logout">Logout</a>
						</dropdown>
				</drop>
				<?php } else { ?>
					<a href="/write">Login</a>
				<?php } ?>
			</nav>
		</header>
		<main>