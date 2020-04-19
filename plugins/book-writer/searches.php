<?php
//get searches by search_id from databse
function get_search($id,$fields = 'all'){
	if (! is_array($field) && $fields != 'all'){
		return false;
	}
	global $wpdb;
	$id = intval($id);
	$table_name = 'searches';
	$result = $wpdb->get_results ( "
	    SELECT * FROM $table_name
	        WHERE ID = $id
	" );
	$return = array();
	$result_arr = get_object_vars($result[0]);
	$allowed_fields = array_keys($result_arr);
	if ($fields == 'all'){
		$fields = $allowed_fields;
	}
	foreach($fields as $field ){
		if (! in_array($field,$allowed_fields)){
			return false;
		}
		if ($field == 'args'){
			$return['args'] = unserialize($result_arr['args']);
		}
		else{
			$return[$field] = $result_arr[$field];
		}
	}
	return $return;
}
//get_template_page() wrapper to give exact type & type_id (also considers forced)
function get_type_template(){
	$template_page = get_template_page();
	global $author;
	$return = array();
	if ($template_page == 'taxonomy-collection.php'){
		$return['type'] = 'collection';
		$return['type_id'] = get_queried_object()->term_id;
	}
	else if ($template_page == 'page-search.php'){
		$return['type'] = 'main';
		$return['type_id'] = 0;
	}
	else if ($template_page == 'author/books.php'){
		$return['type'] = 'author-books';
		$return['type_id'] = $author;
	}
	else if ($template_page == 'author/favorites.php'){
		$return['type'] = 'author-favorites';
		$return['type_id'] = $author;
	}
 	global $force_template_page;
 	global $force_template_id;
 	if ($force_template_page != null && $force_template_page != ''){
 		$return['type'] = $force_template_page;
 	}
 	if ($force_template_id != null && $force_template_id != ''){
 		$return['type_id'] = $force_template_id;
 	}
 	if (! isset($return['type']) || ! isset($return['type_id'])){
 		return false;
 	}
 	return $return;
}
//get template page relative to book-writer theme
function get_template_page(){
 	global $template;
	$to_remove = explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/';
	$to_remove = str_replace('\\','/',$to_remove);
	$template = str_replace('\\','/',$template);
	$template_page = str_replace($to_remove,"",$template);
	return $template_page;
}
//Add Search to Database
function log_search($unpacked){
	global $wpdb;
	if (get_type_template() == false){
		return false;
	}
	$type = get_type_template()['type'];
	$type_id = get_type_template()['type_id'];
	$wpdb->insert(
		'searches', 
		array(
			'type' => $type,
			'type_id' => $type_id,
			'timestamp' => current_time('timestamp',true), 
			'IP' => $_SERVER['REMOTE_ADDR'], 
			'args' => serialize($unpacked), 
		)
	);
	$id = $wpdb->insert_id;
	if (! headers_sent() && ! isset($_SESSION) )	{
		session_start();
	}
	if (isset($_SESSION['search_ids']) && is_array($_SESSION['search_ids'])){
		$_SESSION['search_ids'][] = $id;
	}
	else{
		$_SESSION['search_ids'] = array($id);
	}
	$packed = pack_search($unpacked);
	$taxonomies = array('tag','fandom','rating','language','status','genre','character','pairing');
	foreach ($taxonomies as $key => $taxonomy) {
		$options = array('included','excluded');
		foreach ($options as $key => $option) {
			if (isset($packed[$taxonomy . '_' . $option])){
				$terms = explode(',',$packed[$taxonomy . '_' . $option]);
				foreach ($terms as $key => $term) {
					$wpdb->insert(
						'searchparams', 
						array( 
							'search_id' => $id, 
							'parameter' => $taxonomy . '_' . $option, 
							'value' => $term, 
						)
					);
				}
			}
		}
	}
	if (isset($packed['words'])){
		$words_array = explode(',',$packed['words']);
		$wpdb->insert(
			'searchparams', 
			array( 
				'search_id' => $id, 
				'parameter' => 'min_words', 
				'value' => $words_array[0],
			)
		);
		$wpdb->insert(
			'searchparams', 
			array( 
				'search_id' => $id, 
				'parameter' => 'max_words', 
				'value' => $words_array[1],
			)
		);
	}
	$sort = explode('/', $packed['sort']);
	$wpdb->insert(
		'searchparams', 
		array( 
			'search_id' => $id, 
			'parameter' => 'sort_order', 
			'value' => $sort[1],
		)
	);
	$wpdb->insert(
		'searchparams',
		array( 
			'search_id' => $id, 
			'parameter' => 'sort_by', 
			'value' => $sort[0],
		)
	);
	if (isset($packed['search'])){
		$wpdb->insert(
			'searchparams',
			array( 
				'search_id' => $id, 
				'parameter' => 'search', 
				'value' => $packed['search'],
			)
		);
	}
	return $id;
}
//Max Words
function max_search_words($unpacked){
	//Find Word Count
	$words_args = $unpacked;
	$words_args['meta_key'] = 'word-count';
	$words_args['orderby'] = 'meta_value_num';
	$words_args['posts_per_page'] = 1;
	$words_args['order'] = 'DESC';
	$word_query = new WP_Query( $words_args );
	if (empty($word_query->posts)){
		$max_count = 1;
	}
	else{
		$max_count = get_post_meta( $word_query->posts[0]->ID, 'word-count', true );
	}
	return $max_count;
}
//Search API (pack_search,unpack_search)
function pack_search($unpacked){
	//turn in
	$pack = array();

	//Taxonomies
	$taxonomies = array('tag','category','rating','language','status','genre','character','pairing');
	if (isset($unpacked['tax_query']['relation'])){
		unset($unpacked['tax_query']['relation']);
	}
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
			'post__not_in'	 		 => get_stats_of('user_hidden',get_current_user_id()),
		);
		if (isset($packed['search'])){
			$args['s'] = str_replace(" ","+",$packed['search']);
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
			'post__not_in'	 		 => get_stats_of('user_hidden',get_current_user_id()),
		);	
	}
	return $args;
}


/////Saved Searches
function save_search($name,$link){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		update_user_meta(get_current_user_id(),'saved_searches',array(array($name,$link)));
		return;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	if (saved_search_exists($name,$link) != false){
		return;
	}
	$searches[] = array($name,$link);
	update_user_meta(get_current_user_id(),'saved_searches',$searches);
}
function saved_search_exists($name,$link){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return false;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	if (in_array($link,array_column($searches,1))){
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
function delete_search($name_or_link){
	if (! metadata_exists('user',get_current_user_id(),'saved_searches')){
		return;
	}
	$searches = get_user_meta(get_current_user_id(),'saved_searches',true);
	foreach($searches as $key => $search){
		if ($search[0] == $name_or_link || $search[1] == $name_or_link){
			unset($searches[$key]);
			$searches = array_values($searches);
			update_user_meta(get_current_user_id(),'saved_searches',$searches);
			return;
		}
	}
}