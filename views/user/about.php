<?php
$app->template('/views/user/header');
$app->bundle->css('/css/components/divider');
$app->bundle->mix('search-content');
$app->bundle->mix('search-options');
$app->bundle->css('/css/views/author-about');
$app->bundle->css('/css/views/updates-add');
$app->bundle->css('/css/views/updates-content');
$app->bundle->js('/js/views/updates-add');
$app->bundle->js('/js/views/updates-content');
$app->bundle->enqueue();
$user = $app->user;
$is_current_author = intval(get_current_user_id()) === intval($user->ID);
global $book_query;
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
$book_query = new book_query(array(
    'included'		=> array(
        'author'		=> array($user->ID)
    ),
    'per_page'		=> 2
));
$description = get_the_author_meta( 'description', $user->ID );
// $description = 'This is my bio';
?>
<author-main>
    <?php if ($description !== ''){ ?>
    <author-bio>
        <?= $description; ?>
    </author-bio>
    <?php } ?>
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
    <?php
    $app->updates_count = 2;
    $app->template('/subviews/updates');
    ?>
</author-main>