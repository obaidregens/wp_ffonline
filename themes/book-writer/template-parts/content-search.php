<?php
global $book_query;
global $book;
$collections = collection::query(array(
	'book_ids'		=> array($book->ID),
	'types'			=> array('Favorites','Public')
));
?>
<book book_id="<?= $book->ID; ?>">
	<button class="dropdown">
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
	<a href="<?= get_permalink($book->ID); ?>" class="title"><?= $book->post_title; ?></a>
	<span class="author"><?= author_href($book->ID); ?></span>
	<div class="description"><?= $book->post_excerpt; ?></div>
	<book-meta>
		<span tooltip-top="Last Updated"><?= get_the_time('',$book->ID); ?></span>
		<span tooltip-top="Words"><?= get_post_meta($book->ID,'word-count',true); ?></span>
		<span tooltip-top="Collections"><?= count($collections); ?></span>
	</book-meta>
	<tags>
	<?php
	foreach ($book_query->book_tags[$book->ID] as $taxonomy => $terms){
		?>
		<tag-group name="<?= ucfirst($taxonomy) ?>">
			<?= implode('',array_column($terms,'link')); ?>
		</tag-group>
		<?php
	}
	?>
	</tags>
</book>