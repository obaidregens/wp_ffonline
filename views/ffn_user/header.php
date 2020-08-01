<?php
$author_page = $app->type === 'ffn_author' ? 'about' : substr($app->type,11);
$app->bundle = global_bundle('ffn_author-' . $author_page);
$app->bundle->css('css/views/author-header');
$href = home_url( '/ffn@' . $app->author_id  . '/');
?>
<author-name>
	<?= $app->author_name; ?>
</author-name>
<author-nav>
	<a <?= $author_page === 'about' ? 'active' : '' ?> href="<?= $href; ?>">About</a>
	<a <?= $author_page === 'books' ? 'active' : '' ?> href="<?= $href . 'books'; ?>">Books</a>
	<a target="_blank" tooltip-top="FFNet" class="logo-link" rel="nofollow" href="https://fanfiction.net/u/<?= $app->author_id; ?>"><img width="25" src="/content/static/images/ffnlogo.png"/></a>
</author-nav>