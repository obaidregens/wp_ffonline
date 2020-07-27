<?php
$collection = $app->collection;
$author = get_user_by( 'ID', $collection['author'] )->data;
$follows = count(collection::follower_query(array(
    'collection_ids' => array($collection['ID'])
)));
?>
<collection class="grid-item" collection_id="<?= $collection['ID'] ?>">
    <button class="follow-collection options <?= collection_follow::is($collection['ID']) ? 'followed' : '' ?>"></button>
    <a href="<?= collection::link($collection['ID']); ?>" class="title"><?= $collection['title']; ?></a>
    <a href="<?= get_author_posts_url( $author->ID ); ?>" class="author">@<?= $author->user_login; ?></a>
    <collection-meta>
        <span tooltip-top="Updated"><?= human_time_diff( $collection['modified'], time() ); ?></span>
        <span tooltip-top="Books"><?= $collection['count']; ?></span>
        <span tooltip-top="Follows"><?= $follows ?></span>
    </collection-meta>
</collection>