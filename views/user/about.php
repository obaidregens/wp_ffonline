<?php
$app->template('/views/user/header');
$app->bundle->css('/css/components/divider');
$app->bundle->mix('search-content');
$app->bundle->mix('search-options');
$app->bundle->css('/css/views/author-about');
$app->bundle->css('/css/views/updates-add');
$app->bundle->css('/css/views/updates-content');
$app->bundle->js('/js/views/author-about');
$app->bundle->js('/js/views/updates-add');
$app->bundle->js('/js/views/updates-content');
$app->bundle->enqueue();
$user = $app->user;
$is_current_author = intval(get_current_user_id()) === intval($user->ID);
global $book_query;
$href = rtrim(get_author_posts_url($user->ID),'/') . '/';
$book_query = $app->author_stories_query;
$description = htmlspecialchars(get_the_author_meta( 'description', $user->ID ));
?>
<author-main user_id="<?= $user->ID; ?>">
    <?php if ($description !== '' && $is_current_author){ ?>
    <author-bio>
        <a class="edit-about"></a>
        <content><?= $description; ?></content>
        <text-input type="multi" label="About you"></text-input>
        <button label="Save"></button>
        <button label="Cancel"></button>
    </author-bio>
    <?php } else if ($description !== '') { ?>
    <author-bio>
        <content><?= $description; ?></content>
    </author-bio>
    <?php } else if ($is_current_author) { ?>
    <author-bio>
        <a class="edit-about">Tell others something about yourself.</a>
        <text-input type="multi" label="About you"></text-input>
        <button label="Save"></button>
        <button label="Cancel"></button>
    </author-bio>
    <?php } ?>
    <?php if ($book_query->has()) { ?>
    <author-books books="<?= $book_query->count; ?>">
        <script>
        window.collections_data = <?= script_json(json_encode(collection_helpers::js_data())); ?>;
        window.book_collections = <?= script_json(json_encode(collection_helpers::query_by_book(array_column($book_query->books,'ID')))); ?>;
        </script>
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
    <?php
    $app->updates_count = 2;
    $app->template('/subviews/updates');
    ?>
</author-main>