<?php
$collection = $app->collection;
$author = user::get_by( 'ID', $collection->author );
$follows = count(collection_follow::query_by('type_id',$collection->ID));
?>
<collection class="grid-item" collection_id="<?= $collection->ID ?>">
    <button class="follow-collection options <?= collection_follow::exists($collection->ID,get_current_user_id()) ? 'followed' : '' ?>"></button>
    <?php if (in_array($collection->type,['Favorites','Public','Unlisted'])) { ?>
    <button class="share-collection options"></button>
    <?php } ?>
    <?php if (is_current_user($collection->author)) { ?>
    <button class="edit-collection options"></button>
    <?php } ?>
    <a href="<?= collection_helpers::link($collection->ID); ?>" class="title"><?= htmlspecialchars($collection->title); ?></a>
    <a href="<?= get_author_posts_url( $author->ID ); ?>" class="author">@<?= $author->user_login; ?></a>
    <collection-meta>
        <?php if (!in_array($collection->title,['Favorites','Hidden'])) { ?>
        <span tooltip-top="Created"><?= human_time_diff( $collection->created, time() ); ?></span>
        <?php } ?>
        <span tooltip-top="Books"><?= $collection->count; ?></span>
        <span tooltip-top="Follows"><?= $follows ?></span>
    </collection-meta>
</collection>