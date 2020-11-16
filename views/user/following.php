<?php
$app->template('/views/user/header');
$app->bundle->css('/css/views/author-following');
$app->bundle->js('/js/views/author-following');

$follows = follow::query_by('user','user_id',$app->user->ID);
foreach ($follows as $follow) {
    $user = user::get_by( 'ID', $follow->type_id );
    if ($user === false) {
        continue;
    }
    $follows = count(follow::query_by('user','type_id',$user->ID));
    $stories = (new book_query(array(
        'included'		=> array(
            'author'		=> array($user->ID)
        ),
        'per_page'		=> 1
    )))->count;
    $votes = count(vote::all_votes($user->ID));
    ?>
    <author-single>
        <author-meta>
            <a href="/@<?=$user->user_login; ?>">@<?=$user->user_login; ?></a>
            <span><?=$stories;?> Stories - <?=$follows?> Follows - <?=$votes?> Votes</span>
        </author-meta>
        <button user_id="<?=$user->ID; ?>" class="author-follow followed"></button>
    </author-single>
    <?php
}