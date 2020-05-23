<?php
function initialize_custom_cache_dir(){
	if (! defined("custom_cache_dir")){
		define("custom_cache_dir",explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/custom-cache');
	}
	if (! file_exists(custom_cache_dir)){
		mkdir(custom_cache_dir);
	}
}
function add_front_cache(){
	initialize_custom_cache_dir();
    //GET HTML
    ob_start();
	get_template_part('page-search');
	$raw_html = ob_get_contents();
	$html = $raw_html;
	if (is_ssl()){
		$html = str_replace ('http://','https://',$raw_html);
	}
	ob_end_clean();
    
	file_put_contents(custom_cache_dir . '/' . time(),$html);
	return true;
}
function get_front_cache(){
	initialize_custom_cache_dir();
	$files = array_diff(scandir(custom_cache_dir, SCANDIR_SORT_DESCENDING), array('..', '.'));
	if (empty($files)){
		return false;
	}
	$newest_file = custom_cache_dir . '/' . $files[0];
	return file_get_contents($newest_file);
}