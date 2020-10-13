<?php
function get_data($book_id = 'new'){
	$book = get_post($book_id);
	$all_categories = get_terms(array(
		'taxonomy' => 'category',
		'hide_empty' => false,
		'parent'    => 0,
	));
	$categories = [];
	foreach ($all_categories as $category){
		$categories[$category->term_id] = array(
			'name'		=> $category->name,
			'fandoms'	=> [],
		);
	}
	//Fandoms
	$fandom_lookup = get_terms([
		'taxonomy'		=> 'category',
		'hide_empty'	=> false,
		'exclude'		=> array_column($all_categories,'term_id')
	]);
	$fandom_lookup = array_combine(array_column($fandom_lookup,'term_id'),$fandom_lookup);
	$all_characters = get_terms([
		'taxonomy'			=> 'character',
		'hide_empty'		=> false,
	]);
	$no_character_fandoms = array_diff(array_keys($fandom_lookup),array_column($all_characters,'parent'));
	foreach($no_character_fandoms as $f_id){
		$fandom = &$fandom_lookup[$f_id];
		$categories[$fandom->parent]['fandoms'][$fandom->term_id] = [
			'name'				=> $fandom->name,
			'characters'		=> [],
		];
	}
	foreach ($all_characters as $k => $character) {
		$fandom = &$fandom_lookup[$character->parent];
		$fandom_loc = &$categories[$fandom->parent ?? 0]['fandoms'][$fandom->term_id ?? 0];
		if (!isset($fandom_loc)) {
			$fandom_loc = [
				'name'			=> $fandom->name ?? 'General',
				'characters'	=> [],
			];
		}
		$fandom_loc['characters'][$character->term_id] = [
			'name'			=> $character->name,
		];
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
	$data['selected']['fandom'] = [];
	$data['selected']['characters'] = [];
	$data['selected']['pairing'] = [];

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
				'include'	=> $book_fandom_ids,
				'hide_empty'=> false
			));
		}
		foreach (($book_fandoms ?? []) as $key => $fandom) {
			$s['fandom'][] = [
				'category' 	=> strval($fandom->parent),
				'value'		=> strval($fandom->term_id),
				'label'		=> $fandom->name
			];
		}
		$book_characters = wp_get_post_terms($book->ID,'character');
		$character_name_hash = [];
		foreach ($book_characters as $character ) {
			$character_name_hash[$character->name] = [
				'value'		=> strval($character->term_id),
				'label'		=> $character->name,
				'fandom'	=> strval($character->parent),
				'category'	=> strval($book_categories[$character->parent] ?? 0)
			];
			$s['characters'][] = $character_name_hash[$character->name];
		}

		$book_pairings = pairing::for_books([$book->ID])[$book->ID];
		foreach($book_pairings as $k => $pairing){
			$s['pairing'][$k] = [];
			foreach ($pairing->characters as $character) {
				$s['pairing'][$k][] = [
					'value'		=> strval($character->term_id),
					'label'		=> $character->name,
					'fandom'	=> strval($character->parent),
					'category'	=> strval($book_categories[$character->parent])	
				];
			}
		}

		$s['title'] = $book->post_title;
		$s['description'] = $book->post_excerpt;
		$s['publish'] = $book->post_status === 'publish';
		$s['reviews'] = $book->comment_status === 'open';
		$s['anonymous_reviews'] = get_post_meta( $book_id,'anon_review',true) === 'true';
	}

	//Chapters
	$s['chapters'] = [];
	if ($book_id !== 'new'){
		$all_chapters = published_chapters($book->ID);
		foreach($all_chapters as $k => $chapter){
			$chapter_arr = [
				'ID'				=> $chapter->ID,
				'title'				=> $chapter->post_title,
				'num'				=> $k+1,
				'preAN'				=> get_post_meta( $chapter->ID, 'pre_author_note', true ),
				'postAN'			=> get_post_meta( $chapter->ID, 'post_author_note', true ),
			];
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