<?php
$user = $app->user;
$author_page = $app->type === 'author' ? 'about' : substr($app->type,7);
$app->bundle = global_bundle('author-' . $author_page);
$app->bundle->css('css/views/author-header');
$app->bundle->js('js/views/author-header');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/components/floater');
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
$connection = c_user::current($user->ID);
$is_current_author = is_current_user($user->ID);
$app->author_stories_query = new book_query(array(
    'included'		=> array(
        'author'		=> array($user->ID)
    ),
    'per_page'		=> 2
));
?>
<floater>
	<?php if ($is_current_author) { ?>
	<a class="author-settings" href="<?= $href . 'settings'; ?>"></a>
	<?php } ?>
	<?php if (! $is_current_author) { ?>
	<a class="author-message" href="/inbox/@<?= $user->user_login; ?>"></a>
	<a class="author-follow <?= follow::exists('user',$user->ID) ? 'followed' : '' ?>"></a>
	<?php } ?>
</floater>
<author-name>
	@<?= $user->user_login; ?>
</author-name>
<author-stats>
<stat count="<?= $app->author_stories_query->count; ?>" label="Stories"></stat>
<stat count="<?= count(follow::query_by('user','type_id',$user->ID)) ?>" label="Followers"></stat>
</author-stats>
<author-nav>
	<a <?= $author_page === 'about' ? 'active' : '' ?> href="<?= $href; ?>">About</a>
	<a <?= $author_page === 'stories' ? 'active' : '' ?> href="<?= $href . 'stories'; ?>">Stories</a>
	<a <?= $author_page === 'updates' ? 'active' : '' ?> href="<?= $href . 'updates'; ?>">Updates</a>
	<a <?= $author_page === 'collections' ? 'active' : '' ?> href="<?= $href . 'collections'; ?>">Collections</a>
	<?php if ($connection !== false) { ?>
	<a target="_blank" tooltip-top="FFNet" class="logo-link" rel="nofollow" href="https://fanfiction.net/u/<?= $connection; ?>"><img width="25" src="/content/static/images/ffnlogo.png"/></a>
	<?php } ?>
</author-nav>