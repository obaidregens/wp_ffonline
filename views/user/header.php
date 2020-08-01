<?php
$user = $app->user;
$author_page = $app->type === 'author' ? 'about' : substr($app->type,7);
$app->bundle = global_bundle('author-' . $author_page);
$app->bundle->css('css/views/author-header');
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
?>
<author-name>
	@<?= $user->user_login; ?>
</author-name>
<author-nav>
	<a <?= $author_page === 'about' ? 'active' : '' ?> href="<?= $href; ?>">About</a>
	<a <?= $author_page === 'books' ? 'active' : '' ?> href="<?= $href . 'books'; ?>">Books</a>
	<a <?= $author_page === 'updates' ? 'active' : '' ?> href="<?= $href . 'updates'; ?>">Updates</a>
	<a <?= $author_page === 'collections' ? 'active' : '' ?> href="<?= $href . 'collections'; ?>">Collections</a>
</author-nav>