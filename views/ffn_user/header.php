<?php
$author_page = $app->type === 'ffn_author' ? 'about' : substr($app->type,11);
$app->bundle = global_bundle('ffn_author-' . $author_page);
$app->bundle->css('css/views/author-header');
$app->bundle->css('css/components/tooltips');
$href = home_url( '/ffn@' . $app->author_id  . '/');
$connection = c_user::current();
?>
<?php if ($connection === false) { ?>
<prompt>Is this your account? <a href="/verify">Verify</a></prompt>
<?php } ?>
<author-name>
	<?= $app->author_name; ?>
</author-name>
<author-nav>
	<a <?= $author_page === 'about' ? 'active' : '' ?> href="<?= $href; ?>">About</a>
	<a <?= $author_page === 'stories' ? 'active' : '' ?> href="<?= $href . 'stories'; ?>">Stories</a>
	<a target="_blank" tooltip-top="FFNet" class="logo-link" rel="nofollow" href="https://fanfiction.net/u/<?= $app->author_id; ?>"><img width="25" src="<?= STATIC_URL() ?>images/ffnlogo.png"/></a>
</author-nav>