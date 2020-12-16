</main>
<footer>
	<nav>
		<a href="/help">Help</a>
		<a href="/news">News</a>
		<a href="/rules">Rules</a>
	</nav>
</footer>
<recaptcha-sitekey hidden><?= RECAPTCHA_SITEKEY ?></recaptcha-sitekey>
<script>exposeReCaptcha = () => window.expose.init_reCAPTCHA();</script>
<script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=exposeReCaptcha"></script>
<?php
if (! isset($app->bundle)){
	$app->bundle = global_bundle('global');
}
$app->bundle->print();
if (isset($app->beta_bundle)) {
	$app->beta_bundle->print();
}
if (!is_user_logged_in()) {
	?><script src="https://apis.google.com/js/platform.js" async defer></script><?php
}
?>
<style>
@font-face {
    font-family: 'icons';
    src: url('<?= rtrim(STATIC_URL,'/') ?>/css/fonts/icons.woff?u=1') format('woff');
    font-weight: normal;
    font-style: normal;
    font-display: block;
}
</style>
<?php
if (isset($_GET['debug'])) {
	query_log($_GET['debug'] === "true");
}
?>
</body>
</html>