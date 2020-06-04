<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

function return_code($code,$extra = 0){
	$_return = array(
		'code'			        =>	$code,
		'collection_data'		=>	collection::_present_dashboard()
    );
	if ($extra !== 0){
		$_return['extra'] = $extra;
    }
	echo json_encode($_return);
	exit();
}
$existing_ = collection::query(array(
    'ids'   => array(intval($_POST['collection_id']))
));
if (! is_user_logged_in(  )){
    return_code(15);
}
if (empty($existing_) && $_POST['collection_id'] !== 'new'){
    return_code(7);
}
if ($_POST['collection_id'] !== 'new' && intval($existing_[0]['author']) !== get_current_user_id()){
    return_code(8);
}
if (! in_array($_POST['action'],array('save','delete'))){
    return_code(16);
}
if ($_POST['action'] === 'delete'){
    $return = collection::update($_POST['collection_id'],array(
        'type'                 => 'Trash'
    ));
    return_code(3);
}
//Set Defaults
$_POST['description']   = $_POST['description'] ?? '';
$_POST['title']         = $_POST['title'] ?? '';

if (! isset($_POST['type']) || ! in_array($_POST['type'],array('Public','Private','Unlisted'))){
    return_code(9);
}
if (strlen($_POST['description']) > 400){
    return_code(11);
}
if ($_POST['title'] === ''){
    return_code(12);
}
if (strlen($_POST['title']) > 50){
    return_code(13);
}
$return = 0;
$args = array(
    'title'                => $_POST['title'],
    'description'          => $_POST['description'],
    'type'                 => $_POST['type'],
);
$collection_id = $_POST['collection_id'];
if (! in_array($existing_[0]['title'],array('Favorites','Hidden')) && $_POST['collection_id'] !== 'new'){
    $return = collection::update($_POST['collection_id'],$args);
}
else if ($_POST['collection_id'] === 'new'){
    $return = collection::create($args);
    $collection_id = $return;
}

if (err::is($return)){
    if ($return->errors[0]['error'] == 'Author has collection with same slug.'){
        return_code(1001);
    }
    else if ($return->errors[0]['error'] == 'Slug exists.'){
        return_code(1002);
    }
    return_code(1000);
}

//Won't pass strict_author because check has already been done above
$return = collection::set('collection',$collection_id,isset($_POST['books']) ? $_POST['books'] : array());
if (err::is($return)){
    return_code(2000);
}
if ($_POST['collection_id'] === 'new'){
    return_code(2);
}
return_code(1);