<?php

$app->bundle = new bundle('book');
$app->bundle->mix('jquery');	
$app->bundle->mix('global_new');
$app->bundle->mix('intro');
$app->bundle->mix('search-options');
$app->bundle->css('css/js-components/confirmation');
$app->bundle->js('js/components/confirmation');
$app->bundle->css('css/components/collapsible');
$app->bundle->css('css/components/index');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('/css/components/floater');
$app->bundle->css('css/views/search-tags');
$app->bundle->js('js/views/book-main');
$app->bundle->css('css/views/book-main');
$app->bundle->enqueue();

$book = $app->story;
$book_query = new book_query(array(
    'include_ids'   => array($book->ID)
));
$author = get_user_by( 'ID', $book->post_author );
$all_chapters = published_chapters($book->ID);;
$is_user_logged_in = is_user_logged_in(  );

?>
<book book_id="<?= $book->ID; ?>">
    <book-header>
        <book-title><?= $book->post_title; ?></book-title>
        <author><?= author_href($book); ?></author>
        <?php print_book_meta($book->ID); ?>
        <a class="button" theme href="<?= get_permalink( $all_chapters[0]->ID ); ?>">Start Reading</a>
    </book-header>
    <?php print_book_tags($book->ID,$book_query); ?>
    <book-description><?= $book->post_excerpt; ?></book-description>
    <floater>
        <collections_data hidden>
            <?= json_encode(collection_helpers::js_data()) ?>
        </collections_data>
        <book_collections hidden>
            <?= json_encode( collection_helpers::query_by_book( array($book->ID) ) ); ?>
        </book_collections>
        <button class="book-collections"></button>
        <button class="book-share"></button>
    </floater>
    <input class="collapsible" type="checkbox">
    <label>Chapter Index</label>
    <collapsible>
        <?php
        $app->template('subviews/chapter-index');
        ?>
    </collapsible>
</book>