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
$is_admin = in_array($user->user_login,["admin","ffonline","mods"]);
$follow_class = follow::exists('user',$user->ID) ? 'followed' : '';
?>
<floater>
	<?php if ($is_current_author) { ?>
	<a class="author-settings" href="<?= $href . 'settings'; ?>"></a>
	<?php } ?>
	<?php if (! $is_current_author) { ?>
	<a class="author-message" href="/inbox/@<?= $user->user_login; ?>"></a>
	<a class="author-follow <?= $follow_class; ?>"></a>
	<?php } ?>
</floater>
<author-name user_id="<?= $user->ID; ?>">
	@<?= $user->user_login; ?>
</author-name>
<?php if (!$is_admin) { ?>
<author-stats>
	<stat count="<?= $app->author_stories_query->count; ?>" label="Stories"></stat>
	<stat count="<?= count(follow::query_by('user','type_id',$user->ID)) ?>" label="Followers"></stat>
	<stat count="<?= count(vote::all_votes($user->ID)) ?>" label="Votes"></stat>
</author-stats>
<?php } ?>
<author-nav>
	<a <?= $author_page === 'about' ? 'active' : '' ?> href="<?= $href; ?>">About</a>
	<?php if (!$is_admin) { ?>
		<a <?= $author_page === 'stories' ? 'active' : '' ?> href="<?= $href . 'stories'; ?>">Stories</a>
	<?php } ?>
	<a <?= $author_page === 'updates' ? 'active' : '' ?> href="<?= $href . 'updates'; ?>">Updates</a>
	<?php if (!$is_admin) { ?>
	<a <?= $author_page === 'collections' ? 'active' : '' ?> href="<?= $href . 'collections'; ?>">Collections</a>
	<?php } ?>
	<?php if ($is_admin) { ?>
	<button class="author-follow <?= $follow_class; ?>"></button>
	<?php } ?>
</author-nav>