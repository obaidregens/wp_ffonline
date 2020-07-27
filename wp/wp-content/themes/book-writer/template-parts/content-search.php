<?php
global $book_query;
global $book;
$search = $book_query->args['search'];
?>
<book class="grid-item" book_id="<?= $book->ID; ?>">
	<button class="dropdown options">
		<dropdown class="right">
			<?php if ( intval($book->post_author) === get_current_user_id() ) { ?>
				<a href="/dashboard/write/<?= $book->ID; ?>" target="_blank" label="Edit"></a>
			<?php } ?>
			<li label="Share"></li>
			<li label="Favorite"></li>
			<li label="Hide"></li>
			<li label="Collections"></li>
		</dropdown>
	</button>
	<a href="<?= get_permalink($book->ID); ?>" class="title"><?= mark_search($book->post_title, $search); ?></a>
	<span class="author"><?= author_href($book->ID); ?></span>
	<div class="description"><?= mark_search($book->post_excerpt, $search); ?></div>
	<?php print_book_meta($book->ID); ?>
	<?php print_book_tags($book->ID,$book_query); ?>
</book>