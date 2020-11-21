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
<!-- Migrating -->
<style>
dark-mode{right:30px;z-index:53;}migration{display:flex;position:fixed;left:0;top:53px;width:100%;background-color:#bfe5ff;z-index:52;height:30px}@media only screen and (max-width:500px){migration{top:0}}migration>a{padding-right:10px;font-size:.75rem}migration>text,migration>a{display:flex;align-items:center;white-space:break-spaces;font-size:.85rem}migration>text{color:#262828;padding-left:20px;flex:1}migration>button{border-radius:0;padding:0;margin:0;background-color:transparent;height:30px;width:30px;color:var(--darkgrey)}migration>button::before{display:inline-block;font-family:'icons';content:"\e900";transform:scale(1.6)}
</style>
<script>
document.documentElement.appendChild(DOM.create("migration",{children:[DOM.create("text",{innerHTML:"We'll be migrating servers soon. <a href='/@admin/updates'>Learn more.</a>"}),DOM.create("button",{listeners:{click:({target:e})=>e.parentElement.remove()}})]}));
</script>
</body>
</html>