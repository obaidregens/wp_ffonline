<?php
function add_front_cache(){
    global $wpdb;
    
    //GET HTML
    ob_start();
    get_template_part('page-search');
    $raw_html = ob_get_contents();
    $html = str_replace ('http://','https://',$raw_html);
	ob_end_clean();
    
	$wpdb->insert(
		'custom_cache', 
		array(
			'field' => 'front_page',
			'value' => $html,
			'timestamp' => current_time('timestamp',true), 
		)
	);
	return true;
}
function get_front_cache(){
	global $wpdb;
	
	$table_name = 'custom_cache';
	$field = 'front_page';
	$result = $wpdb->get_results ( "
	    SELECT * FROM $table_name ORDER BY ID DESC LIMIT 1
	" );
	if (is_null($result) || empty($result)){
		return null;
	}
	return $result[0]->value;
}