<?php
global $book_query;
global $book;
global $post;
$is_search = isset($book_query);
if ($is_search){
	$post = $book;
}
else {
	$book_query = new book_query(array(
		'include_ids'	=> array($post->ID)
	));
}
if ( $is_search ){
    ?><div class="home-tags"><div><?php
}
else{ 
	?>
	<div class="book-tags"><div>
	<?php
}
$collections = collection::query(array(
	'book_ids'		=> array($post->ID),
	'types'			=> array('Favorites','Public')
));
echo '<span class="each-tag icon-tag"><i class="fas fa-clock"></i>' . get_the_time() . '</span> ';
echo '<span class="each-tag icon-tag"><i class="fas fa-book-open"></i>' . get_post_meta($post->ID,'word-count',true) . '</span> ';
echo '<span class="each-tag icon-tag"><i class="fas fa-list-ul"></i>' . count($collections) . '</span> ';
if (! $is_search){
	echo '<br>';
}
?></div><?php
foreach ($book_query->book_tags[$post->ID] as $taxonomy => $terms){
	$sep = $taxonomy === 'fandom' ? '/' : ', ';
	?><span class="each-tag"><strong><?= ucfirst($taxonomy); ?>: </strong><?= implode($sep,array_column($terms,'link')); ?></span> <?php
	if (! $is_search){
		echo '<br>';
	}
}
?>
</div>