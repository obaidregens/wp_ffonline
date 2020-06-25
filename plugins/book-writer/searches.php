<?php
function packed_to_url($packed){
	if (isset($packed['page'])){
		unset($packed['page']);
	}
	$construct = '?';
	foreach ($packed as $key => $value) {
		$construct .= $key . '=' . $value . '&';
	}
	$construct .= 'page=1';
	return $construct;
}
//Max Words
function max_search_words($unpacked){
	//Find Word Count
	$words_args = $unpacked;
	$words_args['meta_key'] = 'word-count';
	$words_args['orderby'] = 'meta_value_num';
	$words_args['posts_per_page'] = 1;
	$words_args['paged'] = 1;
	$words_args['order'] = 'DESC';
	$words_args['fields'] = 'ids';
	$word_query = (new WP_Query( $words_args ))->posts;
	if (empty($word_query)){
		$max_count = 1;
	}
	else{
		$max_count = get_post_meta( $word_query[0], 'word-count', true );
	}
	return intval($max_count);
}
//Search API (pack_search,unpack_search)
function pack_search($unpacked){
	$unpacked = json_decode(json_encode( $unpacked ),true);
	//turn in
	$pack = array();
	//Taxonomies
	$taxonomies = array('tag','category','rating','language','status','genre','character','pairing');
	unset($unpacked['tax_query']['relation']);
	if (isset($unpacked['tax_query'])){
		foreach ($unpacked['tax_query'] as $key => $value) {
			if (! in_array($value['taxonomy'],$taxonomies)){
				return false;
			}
			if ($value['field'] != 'term_id'){
				return false;
			}
			if ($value['taxonomy'] == 'category'){
				$taxonomy = 'fandom';
			}
			else{
				$taxonomy = $value['taxonomy'];
			}
			if ($value['operator'] == 'NOT IN'){
				$pack_key = $taxonomy . '_' . 'excluded';
			}
			else if ($value['operator'] == 'AND'){
				$pack_key = $taxonomy . '_' . 'included';
			}
			else{
				return false;
			}
			$pack[$pack_key] = implode(',',$value['terms']);
		}
	}
	//Meta (words)
	if (isset($unpacked['meta_query']['relation'])){
		unset($unpacked['meta_query']['relation']);
	}
	$words_array = array();
	if (isset($unpacked['meta_query'])){
		foreach ($unpacked['meta_query'] as $key => $value) {
			if ( ! isset($value['key']) || $value['key'] != 'word-count' || $value['type'] != 'NUMERIC'){
				continue;
			}
			if ($value['compare'] == '>='){
				$words_array[0] = $value['value'];
			}
			else if ($value['compare'] == '<='){
				$words_array[1] = $value['value'];
			}
			else{
				return false;
			}
			if (count($words_array) == 2){
				break;
			}
		}
	}
	if (! empty($words_array)){
		$pack['words'] = implode(',',$words_array);
	}

	//Search Terms
	//'s' parameter not in use, kept for precaution
	if (isset($unpacked['s'])){
		$pack['search'] = $unpacked['s'];
	}

	//Page Terms
	if (isset($unpacked['paged'])){
		$pack['page'] = intval($unpacked['paged']);
	}

	//Sort Terms
	if (isset($unpacked['orderby']) && isset($unpacked['order'])){
		$sort_options = ['modified/DESC','date/DESC','favorites/DESC','words/DESC'];
		$pack['sort'] = $unpacked['orderby'] . '/' . $unpacked['order'];
		if (! in_array($pack['sort'],$sort_options)){
			return false;
		}
	}
	else{
		$pack['sort'] = 'modified/DESC';
	}
	return $pack;
}
function unpack_search($packed){
	if (! empty($packed)){
		$taxonomies = ['tag','category','rating','language','status','genre','character','pairing'];
		if (isset($packed['words'])){
    		$words_array = explode(',',$packed['words']);

    		if (count($words_array) != 2 || ! is_numeric($words_array[0]) || ! is_numeric($words_array[1]) ){
    			return false;
    		}
    		$meta_query = array(
    			'relation' => 'AND',
    			array(
    				'key'     => 'word-count',
    				'value'   => intval($words_array[0]),
    				'compare' => '>=',
    				'type'    => 'NUMERIC',
    			),
    			array(
    				'key'     => 'word-count',
    				'value'   => intval($words_array[1]),
    				'compare' => '<=',
    				'type'    => 'NUMERIC',
    			),
    		);
		}
		else{
    		$meta_query = array(
    			'relation' => 'AND',
    			array(
    				'key'     => 'word-count',
    				'value'   => 0,
    				'compare' => '>=',
    				'type'    => 'NUMERIC',
    			),
    			array(
    				'key'     => 'word-count',
    				'value'   => 'TBD',
    				'compare' => '<=',
    				'type'    => 'NUMERIC',
    			),
    		);
		}
		$tax_query = array(
			'relation' => 'AND',
		);
		if (isset($packed['fandom_included'])){
			$packed['category_included'] = $packed['fandom_included'];
		}
		if (isset($packed['fandom_excluded'])){
			$packed['category_excluded'] = $packed['fandom_excluded'];
		}
		foreach ($taxonomies as $taxonomy){
			$included = array();
			if (isset($packed[$taxonomy . '_included'])){
				$included = explode(',',$packed[$taxonomy . '_included']);
				foreach($included as $key => $value){
					$included[$key] = intval($value);
				}
			}
			$excluded = array();
			if (isset($packed[$taxonomy . '_excluded'])){
				$excluded = explode(',',$packed[$taxonomy . '_excluded']);
				foreach($excluded as $key => $value){
					$excluded[$key] = intval($value);
				}
			}
			if (empty($included) == false){
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'terms'            => $included,
					'field'            => 'term_id',
					'operator'         => 'AND',
				);
			}
			if (empty($excluded) == false){
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'terms'            => $excluded,
					'field'            => 'term_id',
					'operator'         => 'NOT IN',
				);
			}
		}
		$args = array(
			'post_type'              => array( 'book' ),
			'post_status'            => array( 'publish' ),
			'posts_per_page'		 => 10,
			'post__not_in'	 		 => collection::get_hidden(),
		);
		if (isset($packed['search'])){
			$search = $packed['search'];
			$search_sql = '%' . $search . '%';
			global $wpdb;
			$sql = $wpdb->prepare( "SELECT ID FROM wp_posts WHERE post_title LIKE %s OR post_excerpt LIKE %s",array($search_sql,$search_sql) );
			$results = $wpdb->get_results( $sql ,ARRAY_A );
			$ids = [];
			foreach($results as $result){
				$ids[] = $result['ID'];
			}
			if (isset($args['post__in'])){
				$args['post__in'] = array_merge($args['post__in'],$ids);
			}
			else{
				$args['post__in'] = $ids;
			}
			if (empty($ids)){
				$args['post__in'] = array(0);
			}
			$args['search_ids'] = $ids;
			$args['search_key'] = $search;
		}
		if (isset($packed['page'])){
			$args['paged'] = intval($packed['page']);
		}
		else{
			$args['paged'] = 1;
		}
		$args['tax_query'] = $tax_query;
		if (isset($packed['sort'])){
			$sort_options = ['modified/DESC','date/DESC','favorites/DESC','words/DESC'];
			if (! in_array($packed['sort'],$sort_options)){
				return false;
			}
			$sort = explode('/',$packed['sort']);
			$args['order'] = $sort[1];	
			$args['orderby'] = $sort[0];
		}
		else{
			$args['order'] = 'DESC';
			$args['orderby'] = 'modified';
		}
		if (isset($packed['words'])){
			$args['meta_query'] = $meta_query;
		}
	}
	else{
		$args = array(
			'post_type'      		 => array( 'book' ),
			'post_status'            => array( 'publish' ),
			'posts_per_page' 		 => 10,
			'order'                  => 'DESC',
			'orderby'                => 'modified',
			'post__not_in'	 		 => collection::get_hidden(),
		);	
	}
	return $args;
}
function type_args($args,$placeholder){
	if ($placeholder['type'] === 'home'){}
	else if ($placeholder['type'] === 'collection'){
		$collection_books = array_column(collection::book_query(array($placeholder['type_id'])),'ID');
		$args['include_ids'] = isset($args['include_ids']) ? a_intersect($args['include_ids'],$collection_books) : $collection_books;
	}
	else if ($placeholder['type'] === 'author-books') {
		$args['included']['author'] = isset($args['included']['author']) ? a_intersect($args['included']['author'],array($placeholder['type_id'])) : array($placeholder['type_id']);
	}
	else{
		$error = new err();
		$error->add('type','Unknown type for search: ' . $placeholder['type']);
		return $error;
	}
	return $args;
}
////Tags Data
function tags_data($book_query){
	$ids = $book_query->ids;
	$terms = array();
	if (! empty($ids)){
		$terms = (new tag_query($ids))->terms_with_count;
	}
	return $terms;
}
/////Saved Searches
function save_search($name,$e_args){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		update_user_meta(get_current_user_id(),'saved_searches',array(array($name,$e_args)));
		return;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	if (saved_search_exists($name,$e_args) != false){
		return false;
	}
	$searches[] = array($name,$e_args);
	update_user_meta(get_current_user_id(),'saved_searches',$searches);
	return true;
}
function saved_search_exists($name,$e_args){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return false;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	if (in_array($e_args,array_column($searches,1))){
		return 'Search exists.';
	}
	else if (in_array($name,array_column($searches,0))){
		return 'Duplicate name.';
	}
	else{
		return false;
	}
}
function get_saved_searches(){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return array();
	}
	return get_user_meta(get_current_user_id(),'saved_searches',true);
}
function delete_search($name_or_args){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	foreach($searches as $key => $search){
		if ($search[0] == $name_or_args || $search[1] == $name_or_args){
			unset($searches[$key]);
			$searches = array_values($searches);
			update_user_meta(get_current_user_id(),'saved_searches',$searches);
			return;
		}
	}
}