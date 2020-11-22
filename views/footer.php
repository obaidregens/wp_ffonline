</main>
<footer>
	<nav>
		<a href="/faq">Help</a>
		<a href="/news">News</a>
		<a href="/rules">Rules</a>
	</nav>
</footer>
<recaptcha-sitekey hidden><?= RECAPTCHA_SITEKEY ?></recaptcha-sitekey>
<script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=init_reCAPTCHA"></script>
<?php
if (! isset($app->bundle)){
	$app->bundle = global_bundle('global');
}
$app->bundle->print();
if (isset($app->beta_bundle)) {
	$app->beta_bundle->print();
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
</body>
</html>