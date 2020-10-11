<?php
$app->template('/views/ffn_user/header');
$app->bundle->mix('search-content');
$app->bundle->mix('search-options');
$app->bundle->css('css/views/author-about');
$app->bundle->enqueue();
global $book_query;
$href = home_url( '/ffn@' . $app->author_id  . '/');
$book_query = $app->author_books;
?>
<author-main>
    <?php if ($book_query->has()) { ?>
    <author-books books="<?= $book_query->count; ?>">
        <collections_data hidden><?= json_encode(collection_helpers::js_data()) ?></collections_data>
        <books-container class="grid">
            <book_collections hidden><?= json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID'))); ?></book_collections>
            <?php
            global $book;
            foreach ($book_query->books as $book) {
                $app->template( '/subviews/story-single' );
            }
            ?>
        </books-container>
        <?php if ($book_query->count > 2) { ?>
            <a href="<?= $href; ?>stories" class="button more"></a>
        <?php } ?>
    </author-books>
    <?php } ?>
</author-main>