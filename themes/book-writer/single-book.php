<?php

global $bundle;
$bundle = new bundle('book');
$bundle->mix('jquery');	
$bundle->mix('global_new');
$bundle->mix('search-options');
$bundle->css('css/components/collapsible');
$bundle->css('css/components/index');
$bundle->css('css/views/search-tags');
$bundle->js('js/views/book-main');
$bundle->css('css/views/book-main');
$bundle->enqueue();

$book = $post;
$book_query = new book_query(array(
    'include_ids'   => array($book->ID)
));
$author = get_user_by( 'ID', $book->post_author );
$all_chapters = published_chapters($book->ID);;
$is_user_logged_in = is_user_logged_in(  );

get_header();
?>
<main>
<book book_id="<?= $book->ID; ?>">
    <book-header>
        <book-title><?= $book->post_title; ?></book-title>
        <author><?= author_href($book); ?></author>
        <?php print_book_meta($book->ID); ?>
        <a class="button" theme href="<?= get_permalink( $all_chapters[0]->ID ); ?>">Start Reading</a>
    </book-header>
    <?php print_book_tags($book->ID,$book_query); ?>
    <book-description><?= $book->post_excerpt; ?></book-description>
    <book-options>
        <collections_data hidden>
            <?= json_encode(collection::js_data()) ?>
        </collections_data>
        <book_collections hidden>
            <?= json_encode( collection::query_by_book( array($book->ID), 'ID' ) ); ?>
        </book_collections>
        <button theme class="book-collections"></button>
        <button theme class="book-share"></button>
    </book-options>
    <input class="collapsible" type="checkbox">
    <label>Chapter Index</label>
    <collapsible>
        <index>
            <li>
                <cell>#</cell>
                <cell>Chapter</cell>
                <cell>Words</cell>
                <cell>Reviews</cell>
            </li>
            <?php foreach ($all_chapters as $key => $link_chapter ) { ?>
                <a href="<?= get_permalink( $link_chapter->ID ); ?>">
                    <cell><?= $key+1; ?></cell>
                    <cell><?= $link_chapter->post_title; ?></cell>
                    <cell><?= get_post_meta($link_chapter->ID,'word-count',true); ?></cell>
                    <cell><?= get_comments_number($link_chapter->ID); ?></cell>
                </a>
            <?php } ?>
        </index>
    </collapsible>
</book>
</main>
<?php
get_footer();