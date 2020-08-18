<?php
if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}
$landing = new _landing();
$landing_key = $landing->encrypt();

//Nonce
$nonce = bin2hex(random_bytes(14));
if (! isset($_SESSION['nonce']) || ! is_array($_SESSION['nonce'])){
	$_SESSION['nonce'] = [];
}
$_SESSION['nonce'][] = $nonce;
$is_user_logged_in = is_user_logged_in();
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
				<a href="/write">Write</a>
				<a href="/collections">Collections</a>
			</nav>
		</header>
		<main>