<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
function return_code($code,$extra = 0){
	$_return = array(
		'code'			=>	$code,
		'notifications'	=>  notifications::get()
	);
	if ($extra !== 0){
		$_return['extra'] = $extra;
	}
	echo json_encode($_return);
	exit();
}
$types = _landing::decrypt($_POST['data']);
if ($types === null){
    return_code(9);
}
$_POST['im_books'] = isset($_POST['im_books']) ? $_POST['im_books'] : array();
$_POST['im_collections'] = isset($_POST['im_collections']) ? $_POST['im_collections'] : array();

$instance = new _action($types->landing_id);
foreach ($_POST['im_books'] as $key => $book_id) {
    $instance->log_impression('book',$book_id);
}
foreach ($_POST['im_collections'] as $key => $collection_id) {
    $instance->log_impression('collection',$collection_id);
}
$instance->log_view($types->type,$types->type_id);
if ($_POST['notification_open'] === 'true'){
	$instance->log_notifications($types->type,$types->type_id);
}
return_code(1);