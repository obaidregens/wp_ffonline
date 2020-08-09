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
<a label="Drafts" href="/drafts" class="button"></a>
<?php foreach ($books as $book) { ?>
<a href="/my-books/<?= $book->ID; ?>" class="book">
<book-title><?= $book->post_title; ?></book-title>
<book-status><?= $book->post_status === 'publish' ? 'Published' : 'Saved'; ?></book-status>
<book-views>
    <stat timespan="Last Week">16</stat>
    <stat timespan="All Time">87</stat>
</book-views>
</a>
<?php } ?>
<?php if (empty($books)) { ?>
No books
<?php } ?>