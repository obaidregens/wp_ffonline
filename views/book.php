<?php

$app->bundle = global_bundle('book');
$app->bundle->mix('search-options');
$app->bundle->mix('confirmation');
$app->bundle->css('css/components/collapsible');
$app->bundle->css('css/components/index');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('/css/components/floater');
$app->bundle->css('css/views/search-tags');
$app->bundle->js('js/views/book-main');
$app->bundle->css('css/views/book-main');
$app->bundle->js('js/views/story-offlineAPI');
$app->bundle->js('js/views/book-offline');
$app->bundle->enqueue();

$book = $app->story;
$book_query = new book_query(array(
    'include_ids'   => array($book->ID)
));
$all_chapters = published_chapters($book->ID);;
$is_user_logged_in = is_user_logged_in(  );

?>
<book book_id="<?= $book->ID; ?>">
    <book-header>
        <book-title><?= htmlspecialchars($book->post_title); ?></book-title>
        <author><?= author_href($book); ?></author>
        <book-description><?= htmlspecialchars($book->post_excerpt); ?></book-description>
        <?php print_book_meta($book->ID); ?>
        <?php print_book_tags($book->ID,$book_query); ?>
    </book-header>
    <book-more>
        <book-options>
            <a class="button" href="<?= get_permalink( $all_chapters[0]->ID ); ?>">Read</a>
            <button class="book-collections"></button>
            <button class="book-share"></button>
            <button class="book-offline"></button>
        </book-options>
        <chapter-index>
            <input class="collapsible" type="checkbox">
            <label>Chapter Index</label>
            <collapsible>
                <?php
                $app->template('subviews/chapter-index');
                ?>
            </collapsible>
        </chapter-index>
    </book-more>
    <script>
        window.collections_data = <?= script_json(json_encode(collection_helpers::js_data())); ?>;
        window.book_collections = <?= script_json(json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID')))); ?>;
    </script>
</book>