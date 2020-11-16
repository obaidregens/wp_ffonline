<?php
$app->template('/views/ffn_user/header');
$app->bundle->mix('search-content');
$app->bundle->mix('story-options');
$app->bundle->css('css/views/author-about');
global $book_query;
$href = home_url( '/ffn@' . $app->author_id  . '/');
$book_query = $app->author_books;
?>
<author-main>
    <?php if ($book_query->has()) { ?>
    <author-books books="<?= $book_query->count; ?>">
        <books-container class="grid">
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