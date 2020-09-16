<?php
global $book_query;
global $book;
$search = $book_query->args['search'];
?>
<book class="grid-item" book_id="<?= $book->ID; ?>">
	<button class="dropdown options">
		<dropdown class="right">
			<li tabindex="0" label="Share"></li>
			<li tabindex="0" label="Favorite"></li>
			<li tabindex="0" label="Hide"></li>
			<li tabindex="0" label="Collections"></li>
		</dropdown>
	</button>
	<a href="<?= get_permalink($book->ID); ?>" class="title"><?= mark_search($book->post_title, $search); ?></a>
	<span class="author"><?= author_href($book->ID); ?></span>
	<div class="description"><?= mark_search($book->post_excerpt, $search); ?></div>
	<?php print_book_meta($book->ID); ?>
	<?php print_book_tags($book->ID,$book_query); ?>
</book>