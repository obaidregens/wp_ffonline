<?php
global $post;
?>
<div class="book-tags">
	<div>
<?php
$book_query = new book_query(array(
	'include_ids'	=> array($post->ID)
));
$collections = collection_books::query_by('book_id',$post->ID);
?>
<span class="each-tag icon-tag"><i class="fas fa-clock"></i><?= get_the_time(); ?></span>
<span class="each-tag icon-tag"><i class="fas fa-book-open"></i><?= get_post_meta($post->ID,'word-count',true) ?></span>
<span class="each-tag icon-tag"><i class="fas fa-list-ul"></i><?= count($collections); ?></span>
<br>
</div><?php
foreach ($book_query->book_tags[$post->ID] as $taxonomy => $terms){
	$sep = $taxonomy === 'fandom' ? '/' : ', ';
	?>
	<span class="each-tag"><strong><?= ucfirst($taxonomy); ?>: </strong><?= implode($sep,array_column($terms,'link')); ?></span> 
	<br>
	<?php
}
?>
</div>