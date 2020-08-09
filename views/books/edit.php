<?php
$app->bundle = global_bundle('edit-book');
$app->bundle->css('css/js-components/switch');
$app->bundle->js('js/components/switch');
$app->bundle->css('css/views/book-edit');
$app->bundle->js('js/views/book-edit');
$app->bundle->enqueue();
$book = $app->book;
?>
<book book_id="<?= $book->ID; ?>">
    <a href="<?= get_permalink( $book->ID ); ?>" class="book-title"><?= $book->post_title; ?></a>
    <switch label="Reviews" <?= $book->comment_status === 'open' ? 'checked' : ''; ?>></switch>
    <switch label="Publish" <?= $book->post_status === 'publish' ? 'checked' : ''; ?>></switch>
</book>