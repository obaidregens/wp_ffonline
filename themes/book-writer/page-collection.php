<?php
/**
 * Template Name: Collection Archive
 *
 * Search page
 *
 */
get_header();
$args = array(
    'taxonomy' 		=> 'collection',
    'hide_empty' 	=> true,
	'meta_query' 	=> array(
		'relation' 		=> 'AND',
		array(
			'key'     	=> 'public_collection',
			'value'   	=> 'Public',
			'compare' 	=> '=',
			'type'    	=> 'CHAR',
		),
	),
);
if (isset($_GET['sortby']) && $_GET['sortby']){
	if ($_GET['sortby'] == 'most-books'){
		$args['orderby'] = 'count';
		$args['order'] = 'DESC';
	}
	else if($_GET['sortby'] == 'least-books'){
		$args['orderby'] = 'count';
		$args['order'] = 'ASC';		
	}
	else if ($_GET['sortby'] == 'newest'){
		$args['meta_key'] = 'time_modified';
		$args['orderby'] = 'meta_value_num';
		$args['order'] = 'DESC';
	}
}
$collections = get_terms($args);
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php echo '<div class="row"><h2 class="center-align col s12" >Collections</h2></div>'; ?>
		<div class="row">
			<div class="col s12">
				<select style="background-image:none;" onChange="window.location.href='?sortby=' + this.value">
					<option value="" disabled selected>Sort by</option>
					<option value="most-books">Most Books</option>
					<option value="least-books">Least Books</option>
					<option value="newest">Newest</option>
				</select>
			</div>
		</div>
		<div class="row"><div id="box" class="col s12">
			<?php
			if (! empty($collections)){
                foreach($collections as $collection){
                    global $collection;
                    get_template_part( 'template-parts/content', 'collection' );
                }
			}
			else {
				get_template_part( 'template-parts/content', 'nocollections' );
			}
			?>
		</div></div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php //get_template_part( 'template-parts/content', 'bookpaginate' ); ?>

<?php
	get_footer();
?>	
	
