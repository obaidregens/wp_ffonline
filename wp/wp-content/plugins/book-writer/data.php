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
			'username'	=> '@' . get_userdata( get_current_user_id() )->user_login
		)
	);
	//Other Tags
	//Rating, Language, Status, Genre
	$taxonomies = array('rating','language','status','genre','tag');
	foreach($taxonomies as $taxonomy){
		$data['all'][$taxonomy] = [];
		$all_terms = get_terms(array(
			'taxonomy'		=> $taxonomy,
			'hide_empty'	=> false
		));
		foreach($all_terms as $term){
			$data['all'][$taxonomy][$term->term_id] = array(
				'name'		=> $term->name,
			);
		}
		// Selected
		if ($book_id != 'new'){
			$book_tax = wp_get_object_terms($book->ID,$taxonomy,array(
				'fields'	=> 'id=>name'
			));
		}
		$data['selected'][$taxonomy] = [];
		foreach (($book_tax ?? []) as $term_id => $term_name) {
			$data['selected'][$taxonomy][] = [
				'label'	=> $term_name,
				'value'	=> strval($term_id)
			];
		}
	}

	//Selected
	//Categories
	$data['selected']['fandom'] = array();
	$data['selected']['characters'] = array();
	$data['selected']['pairing'] = array();

	$data['selected']['title'] 				  = '';
	$data['selected']['description'] 		  = '';
	$data['selected']['reviews']  			  = false;
	$data['selected']['anonymous_reviews']	  = false;
	$data['selected']['publish'] 			  = false;
	$data['selected']['book_id']			  = $book_id;
	if ($book_id != 'new'){
		$data['selected']['book_id']		  = (int) $book_id;
	}
	if ($book_id != 'new' ){
		$s = &$data['selected'];
		//This also includes categories
		$book_categories = wp_get_post_terms($book->ID,'category',[
			'fields'	=> 'id=>parent'
		]);
		//Will include a value of 0, no harm in keeping it.
		//array_unique is unnecessary as well, unless in_array() performs faster on smaller arrays.
		// array_values gets an array of parents
		$book_category_ids = array_unique(array_values($book_categories));
		
		//Now does not include categories
		// array_keys gets an array of ids
		$book_fandom_ids = array_diff(array_keys($book_categories),$book_category_ids);

		if (! empty($book_fandom_ids)){
			$book_fandoms = get_terms(array(
				'include'	=> $book_fandom_ids
			));
		}
		foreach (($book_fandoms ?? []) as $key => $fandom) {
			$s['fandom'][] = [
				'category' 	=> $fandom->parent,
				'value'		=> $fandom->term_id,
				'label'		=> $fandom->name
			];
		}
		$book_characters = wp_get_post_terms($book->ID,'character');
		$character_name_hash = [];
		foreach ($book_characters as $character ) {
			if (intval($character->parent) === 0) {
				continue;
			}
			$character_name_hash[$character->name] = [
				'value'		=> strval($character->term_id),
				'label'		=> $character->name,
				'fandom'	=> strval($character->parent),
				'category'	=> strval($book_categories[$character->parent])
			];
			$s['characters'][] = $character_name_hash[$character->name];
		}

		$book_pairings = wp_get_post_terms($book->ID,'pairing');
		foreach($book_pairings as $k => $pairing){
			$s['pairing'][$k] = [];
			$pairing_chars = explode('/',$pairing->name);
			foreach ($pairing_chars as $char_name) {
				if (!isset($character_name_hash[$char_name])) {
					continue;
				}
				$s['pairing'][$k][] = $character_name_hash[$char_name];
			}
		}

		$s['title'] = $book->post_title;
		$s['description'] = $book->post_excerpt;
		$s['publish'] = $book->post_status === 'publish';
		$s['reviews'] = $book->comment_status === 'open';
		$s['anonymous_reviews'] = get_post_meta( $book_id,'anon_review',true) === 'true';
	}

	//Chapters
	$s['chapters'] = array();
	if ($book_id !== 'new'){
		$all_chapters = published_chapters($book->ID);
		foreach($all_chapters as $k => $chapter){
			$chapter_arr = array(
				'ID'				=> $chapter->ID,
				'title'				=> $chapter->post_title,
				'num'				=> $k+1
			);
			$s['chapters'][] = $chapter_arr;
		}
	}
    return $data;
}
function get_autosaves($book_id,$chapter_id){
	global $wpdb;
	$table_name = "custom_autosaves";
	$result = $wpdb->get_results ( "
		SELECT * FROM $table_name
			WHERE chapter_id = $chapter_id
			AND book_id = $book_id
			ORDER BY timestamp DESC
	" );
	$return = array();
	foreach ($result as $value) {
		$return[$value->timestamp] = $value->content;
	}
	return $return;
}