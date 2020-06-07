<?php
//Author header
?>

<?php
$template_page = _landing::get_type()['type'];
$author_base = rtrim(get_author_posts_url($author),'/') . '/';
global $wp_query;
?>
<div class="author-header">
	<div class="author-name-block">
		<h1 class="author-title"><?php echo get_the_author_meta('display_name',$author); ?></h1>
	</div>
	<div class="author-menu">
		<div class="author-tabs">
			<a href="<?php echo $author_base; ?>" <?php if ($template_page == 'author'){?> class="active" <?php } ?> >About</a>
			<a href="<?php echo $author_base . 'books'; ?>" <?php if ($template_page == 'author-books'){?> class="active" <?php } ?> >Books</a>
			<a href="<?php echo $author_base . 'updates'; ?>" <?php if ($template_page == 'author-updates'){?> class="active" <?php } ?> >Updates</a>
			<a href="<?php echo $author_base . 'collections'; ?>" <?php if ($template_page == 'author-collections'){?> class="active" <?php } ?> >Collections</a>
		</div>
		<div class="author-actions">
			<!--<a><i class="fas fa-user-plus"></i></a>-->
			<a target="_blank" href="/dashboard/chat/<?php echo $author; ?>"><i class="fas fa-envelope"></i></a>
		</div>
	</div>
</div>