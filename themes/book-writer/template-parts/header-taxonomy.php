<?php
if (is_home() || is_search() || is_archive()){
    ?><div class="home-tags"><div><?php
}
else{ 
	?>
	<div class="book-tags"><div>
	<?php
}
$meta_query = array(
	array(
		'key'     => 'public_collection',
		'value'   => 'Public',
	),
);
$collections = get_terms(array(
    'meta_query' => $meta_query,
    'taxonomy' => 'collection',
    'object_ids' => array($post->ID),
));
echo '<span class="each-tag icon-tag"><i class="fas fa-clock"></i>' . get_the_time() . '</span> ';
echo '<span class="each-tag icon-tag"><i class="fas fa-book-open"></i>' . get_post_meta($post->ID,'word-count',true) . '</span> ';
echo '<span class="each-tag icon-tag"><i class="fas fa-heart"></i>' . count(get_stats_of('book_fav',$post->ID)) . '</span> ';
echo '<span class="each-tag icon-tag"><i class="fas fa-list-ul"></i>' . count($collections) . '</span> ';
if (is_home() == false && is_search() == false && is_archive() == false){
	echo '<br>';
}
?></div><?php
$taxonomies = get_object_taxonomies('book');
array_splice($taxonomies, 0, 1);
//the_terms( $post->ID, 'category', '<strong>Category: </strong> <span id="hide-' . 'category' . '">', '/', ' </span><i id="hide-' . 'category' . '-button"  class="fas fa-minus-square" ></i> ' );
the_terms( $post->ID, 'category', '<span class="each-tag"><strong>Category: </strong> ', '/', '</span> ' );
if (is_home() == false && is_search() == false && is_archive() == false){
	echo '<br>';
}
foreach ($taxonomies as $taxonomy)
{
	//the_terms( $post->ID, $taxonomy, '<strong>' . ucfirst($taxonomy) . ': </strong><span id="hide-' . $taxonomy . '">', ', ', ' </span><i class="fas fa-minus-square" id="hide-' . $taxonomy . '-button"></i> ' );
	//
	if ($taxonomy != 'collection'){
		the_terms( $post->ID, $taxonomy, '<span class="each-tag"><strong>' . ucfirst($taxonomy) . ': </strong>', ', ', '</span>  ' );
			if (is_home() == false && is_search() == false && is_archive() == false){
			echo '<br>';
		}
	}

}
?>
</div>