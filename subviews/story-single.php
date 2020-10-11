<?php
global $book_query;
global $book;
$search = $book_query->args['search'];
$chapters_count = count(published_chapters($book->ID,-1,'ids'));
?>
<book class="waves-effect" book_id="<?= $book->ID; ?>" chapters="<?= $chapters_count; ?>" >
	<a href="<?= get_permalink($book->ID); ?>" class="title"><?= mark_search($book->post_title, $search); ?></a>
	<span class="author"><?= author_href($book->ID); ?></span>
    <div class="description"><?= mark_search($book->post_excerpt, $search); ?></div>
	<?php print_book_meta($book->ID); ?>
    <?php print_book_tags($book->ID,$book_query); ?>
    <reading-progress></reading-progress>
    <options>
        <li class="waves-effect" tabindex="0" label="Read"></li>
        <li class="waves-effect" tabindex="0" label="Favorite"></li>
        <li class="waves-effect" tabindex="0" label="Hide"></li>
        <li class="waves-effect" tabindex="0" label="Share"></li>
        <li class="waves-effect" tabindex="0" label="Offline"></li>
    </options>
</book>