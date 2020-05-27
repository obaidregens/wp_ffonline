<?php
function get_data($book_id = 'new'){
	$book = get_post($book_id);
	$all_categories = get_terms(array(
		'taxonomy' => 'category',
		'hide_empty' => false,
		'parent'    => 0,
	));
	$categories = array();
	foreach ($all_categories as $category){
		$categories[$category->term_id] = array(
			'name'		=> $category->name,
			'fandoms'	=> array()
		);
	}
	//Fandoms
	$all_fandoms = get_terms(array(
		'taxonomy'		=> 'category',
		'hide_empty'	=> false,
		'exclude'		=> array_column($all_categories,'term_id')
	));

	$all_characters = get_terms(array(
		'taxonomy'			=> 'character',
		'hide_empty'		=> false
	));
	foreach($all_fandoms as $fandom){
		$categories[$fandom->parent]['fandoms'][$fandom->term_id] = array(
			'name'				=> $fandom->name,
			'characters'		=> array(),
		);

		// Characters
		// Looping through $all_characters in every fandom loop is a bit inefficient.
		// Alternative 1 - Call get_terms('parent' => term_id) in every fandom loop (More inefficient)
		// Alternative 2 - Drag loop outside fandom loop (Can't nest under fandom)
		// Alternative 3 - Don't make objects nested,just reference parents 
		// (Completely different idea, might implement later if there are issues with the current one)
		foreach($all_characters as $character){
			if ($character->parent == $fandom->term_id){
				
				$categories[$fandom->parent]['fandoms'][$fandom->term_id]['characters'][$character->term_id] = array(
					'name'			=> $character->name,
				);
			}
		}
	}
	$data = array(
		'all'		=> 	array(
			'categories'	=> $categories
		),
		'selected' => array(

		)
	);
	//Other Tags
	//Rating, Language, Status, Genre
	$taxonomies = array('rating','language','status','genre','tag');
	foreach($taxonomies as $taxonomy){
		$book_tax = array();
		if ($book_id != 'new'){
			$book_tax = wp_get_object_terms($book->ID,$taxonomy,array(
				'fields'	=> 'id=>name'
			));
		}
		$all_terms = get_terms(array(
			'taxonomy'		=> $taxonomy,
			'hide_empty'	=> false
		));
		foreach($all_terms as $term){
			$data['all'][$taxonomy][$term->term_id] = array(
				'name'		=> $term->name,
			);
		}
		$data['selected'][$taxonomy] = $book_tax;
	}
	//Selected
	//Categories
	$data['selected']['category'] = array();
	$data['selected']['fandom'] = array();
	$data['selected']['character'] = array();
	$data['selected']['pairing'] = array();

	$data['selected']['title'] 				  = '';
	$data['selected']['description'] 		  = '';
	$data['selected']['detailed_description'] = '';
	$data['selected']['anononymous_reviews']  = false;
	$data['selected']['publish'] 			  = false;
	$data['selected']['book_id']			  = $book_id;
	if ($book_id != 'new'){
		$data['selected']['book_id']		  = (int) $book_id;
	}
	if ($book_id != 'new' ){
		//This also includes categories
		$data['selected']['fandom'] = wp_get_post_terms($book->ID,'category');
		//Will include a value of 0, no harm in keeping it.
		//array_unique is unnecessary as well, unless in_array() performs faster on smaller arrays.
		$book_category_ids = array_unique(array_column($data['selected']['fandom'],'parent'));
		if (! empty($book_category_ids)){
			$data['selected']['category'] = get_terms(array(
				'include'	=> $book_category_ids,
				'fields'	=> 'id=>name'
			));
		}
		
		//Now does not include categories
		$book_fandom_ids = array_diff(array_column($data['selected']['fandom'],'term_id'),$book_category_ids);
		if (! empty($book_fandom_ids)){
			$data['selected']['fandom'] = get_terms(array(
				'include'	=> $book_fandom_ids,
				'fields'	=> 'id=>name'
			));
		}

		$data['selected']['character'] = wp_get_post_terms($book->ID,'character',array(
			'fields'	=> 'id=>name'
		));

		$data['selected']['pairing'] = wp_get_post_terms($book->ID,'pairing',array(
			'fields'	=> 'id=>name'
		));
		foreach($data['selected']['pairing'] as $key => $book_pairing){
			$data['selected']['pairing'][$key] = explode('/',$book_pairing);
		}

		$data['selected']['title'] = $book->post_title;
		$data['selected']['description'] = $book->post_excerpt;
		$data['selected']['detailed_description'] = $book->post_content;
		if ($book->post_status == 'publish'){
			$data['selected']['publish'] = true;
		}
		if (get_post_meta( $book_id,'anon_review',true) == 'true'){
			$data['selected']['anononymous_reviews'] = true;
		}
	}

	//Chapters
	$data['selected']['chapters'] = array();
	$data['selected']['chapters']['published'] = array();
	$data['selected']['chapters']['draft'] = array();
	
	if ($book_id != 'new'){
		$all_chapters = all_chapters($book -> ID);
		foreach($all_chapters as $chapter){
			$chapter_arr = array(
				'ID'				=> $chapter->ID,
				'title'				=> $chapter->post_title,
				'publish_allowed'	=> false,
			);
			if (can_publish_saved_chapter($chapter->ID)){
				$chapter_arr['publish_allowed'] = true;
			}
			if (in_array($chapter->post_status,array('draft','future'))){
				$data['selected']['chapters']['draft'][] = $chapter_arr;
			}
			else if (in_array($chapter->post_status,array('publish'))){
				$data['selected']['chapters']['published'][] = $chapter_arr;
			}
		}
	}
    return $data;
}
function collection_data($book_ids){
	//All Collections, Favorites & Hidden
	$collections = get_terms(array(
		'meta_key' => 'author',
		'meta_value' => get_current_user_id(),
		'taxonomy' => 'collection',
		'hide_empty' => false,
		'fields'	 => 'id=>name'
	));
	foreach ($collections as $id => $name) {
		$collections[$id] = array();
		$collections[$id]['type'] = get_term_meta($id,'public_collection',true);
		$collections[$id]['name'] = $name;
	}
	$collections['favorites'] = array(
		'type'		=> 'Public',
		'name'		=> 'Favorites',
	);
	$collections['hidden'] = array(
		'type'		=> 'Private',
		'name'		=> 'Hidden',
	);
	$book_collections = [];
	$favorites = get_stats_of('user_fav',get_current_user_id());
	$hidden = get_stats_of('user_hidden',get_current_user_id());
	foreach ($book_ids as $book_id) {
		$book_collections[$book_id] = array_intersect(
			wp_get_post_terms($book_id,'collection',array('fields'=>'ids')),
			array_keys($collections)
		);
		if (in_array($book_id,$favorites)){
			$book_collections[$book_id][] = 'favorites';
		}
		if (in_array($book_id,$hidden)){
			$book_collections[$book_id][] = 'hidden';
		}
	}
	return array(
		'book_collections' => $book_collections,
		'collections'	   => $collections,
	);
}
function get_autosaves($book_id,$chapter_id){
	global $wpdb;
	$table_name = "custom_autosaves";
	$result = $wpdb->get_results ( "
		SELECT * FROM $table_name
			WHERE chapter_id = $chapter_id
			AND book_id = $book_id
	" );
	$return = array();
	foreach ($result as $value) {
		$return[$value->timestamp] = $value->content;
	}
	return $return;
}