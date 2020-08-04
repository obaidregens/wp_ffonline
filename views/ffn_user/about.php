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
        <collections_data hidden><?= json_encode(collection::js_data()) ?></collections_data>
        <books-container class="grid">
            <book_collections hidden><?= json_encode(collection::query_by_book(array_column($book_query->books,'ID'),'ID')); ?></book_collections>
            <?php
            global $book;
            foreach ($book_query->books as $book) {
                get_template_part( 'template-parts/content' , 'search' );
            }
            ?>
        </books-container>
        <?php if ($book_query->count > 2) { ?>
            <a href="<?= $href; ?>books" class="button more"></a>
        <?php } ?>
    </author-books>
    <?php } ?>
</author-main>