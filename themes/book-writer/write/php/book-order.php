<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( 'POST' == $_SERVER['REQUEST_METHOD']) {
    $chapters = get_posts(array(
		'authors'		=> $_POST['author'],
		'post_type'		=> 'chapter',
		'post_status'	=> array('publish','draft'),
		'post_parent'	=> $_POST['bookid']
	));
    for ($x = 1; $x <= count($chapters); $x++) {
        update_post_meta($_POST[$x],'chapter_order',$x);
		wp_update_post(array(
			'ID' => $_POST[$x],
			'post_name' => $x,
		));
    }
}
wp_redirect('https://www.fanfiction.online/dashboard/write');
exit();