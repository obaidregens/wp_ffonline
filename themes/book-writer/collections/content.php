<?php
global $collection;
$collection_author = get_userdata($collection['author']);

?>

<article class="collection-content" collection_id="<?= $collection['ID']; ?>">
	<header-c>
		<h2 class="entry-title" ><a href="<?= collection::link($collection['ID']); ?>"><?= $collection['title']; ?></a></h2>
		<h5>by <a href="<?= get_author_posts_url($collection_author->ID); ?>"><?= $collection_author->display_name; ?></a></h5>
	</header-c><!-- .entry-header -->
		
	<div class="search-summary">
		<p><?= $collection['description'] ?></p>
		<div>
            <strong>Books: </strong>
            <?= $collection['count']; ?>
            <strong>Created: </strong>
            <?= human_time_diff($collection['created']); ?> ago</div>
	</div>
</article>