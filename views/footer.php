</main>
<footer>
	<nav>
		<a href="/faq">FAQ</a>
		<a href="/news">News</a>
		<a href="/contact">Contact us</a>
	</nav>
</footer>
<script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=init_reCAPTCHA"></script>
<?php
if (! isset($app->bundle)){
	$app->bundle = global_bundle('global');
	$app->bundle->enqueue();
}
$app->bundle->print();
?>
</body>
</html>