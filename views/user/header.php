<?php
$user = $app->user;
$author_page = $app->type === 'author' ? 'about' : substr($app->type,7);
$app->bundle = global_bundle('author-' . $author_page);
$app->bundle->css('css/views/author-header');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/components/floater');
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
$connection = c_user::current($user->ID);
$is_current_author = intval(get_current_user_id()) === intval($user->ID);
?>
<floater>
	<?php if ($is_current_author) { ?>
	<a class="author-settings" href="<?= $href . 'settings'; ?>"></a>
	<?php } ?>
	<?php if (! $is_current_author) { ?>
	<a class="author-message" href="/inbox/@<?= $user->user_login; ?>"></a>
	<?php } ?>
</floater>
<author-name>
	@<?= $user->user_login; ?>
</author-name>
<author-nav>
	<a <?= $author_page === 'about' ? 'active' : '' ?> href="<?= $href; ?>">About</a>
	<a <?= $author_page === 'stories' ? 'active' : '' ?> href="<?= $href . 'stories'; ?>">Stories</a>
	<a <?= $author_page === 'updates' ? 'active' : '' ?> href="<?= $href . 'updates'; ?>">Updates</a>
	<a <?= $author_page === 'collections' ? 'active' : '' ?> href="<?= $href . 'collections'; ?>">Collections</a>
	<?php if ($connection !== false) { ?>
	<a target="_blank" tooltip-top="FFNet" class="logo-link" rel="nofollow" href="https://fanfiction.net/u/<?= $connection; ?>"><img width="25" src="/content/static/images/ffnlogo.png"/></a>
	<?php } ?>
</author-nav>