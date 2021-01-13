<?php
$app->bundle = global_bundle('my-books');
$app->bundle->css('css/views/my-books');
$books = (new WP_Query([
    'post_type'              => array( 'book' ),
    'post_status'            => array( 'publish','draft' ),
    'posts_per_page'		 => -1,
    'author__in'             => [get_current_user_id()]
]))->posts;
?>
<actions>
    <a label="Import Stories" href="/import-stories" class="button"></a>
    <a label="Drafts" href="/drafts" class="button"></a>
    <a href="/my-stories/new" class="button new-book">New Story</a>
</actions>
<?php foreach ($books as $book) { ?>
<book>
    <a href="/my-stories/<?= $book->ID; ?>" class="book">
        <book-title><?= htmlspecialchars($book->post_title); ?></book-title>
        <book-status><?= $book->post_status === 'publish' ? 'Published' : 'Unpublished'; ?></book-status>
    </a>
    <a href="/my-stories/<?= $book->ID; ?>/stats" class="book-stats"></a>
</book>
<?php } ?>
<?php if (empty($books)) { ?>
No stories yet
<?php } ?>