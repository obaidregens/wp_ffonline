<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['id']) ){
    $book = get_post($_POST['id']);
    if ($book == null || $book->post_type != 'book'){
        exit;
    }
	$chapters = get_posts(array(
	    'post_parent'   => $book->ID,
		'post_type'		=> 'chapter',
		'sort_column'	=> 'post_modified',
		'post_status'	=> array('publish','draft','future'),
		'sort_order'	=> 'DESC',
		'posts_per_page'=> -1,
		'fields'        => 'ids'
	));
	foreach($chapters as $chapter_id){
        wp_update_post(array(
            'ID'    => $chapter_id,
            'post_status' => 'trash'
        ));
	}
    wp_update_post(array(
        'ID'    => $book->ID,
        'post_status' => 'trash'
    ));
    echo 1;
 exit;
}