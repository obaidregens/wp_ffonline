<?php
$app->bundle = global_bundle('my-books');
$app->bundle->css('css/views/my-books');
$app->bundle->enqueue();
$books = (new WP_Query([
    'post_type'              => array( 'book' ),
    'post_status'            => array( 'publish','draft' ),
    'posts_per_page'		 => -1,
    'author__in'             => [get_current_user_id()]
]))->posts;
?>
<actions>
    <a label="Drafts" href="/drafts" class="button"></a>
    <a href="/my-books/new" class="button new-book">New Book</a>
</actions>
<?php foreach ($books as $book) { ?>
<?php $stat = new book_stats($book->ID); ?>
<a href="/my-books/<?= $book->ID; ?>" class="book">
<book-title><?= $book->post_title; ?></book-title>
<book-status><?= $book->post_status === 'publish' ? 'Published' : 'Unpublished'; ?></book-status>
<book-views>
    <stat timespan="Last Week"><?= $stat->view_count(new DateTime('last week')); ?></stat>
    <stat timespan="All Time"><?= $stat->view_count(); ?></stat>
</book-views>
</a>
<?php } ?>
<?php if (empty($books)) { ?>
No books
<?php } ?>