<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
function return_code($code,$extra = 0){
	$_return = array(
		'code'			        =>	$code,
    );
	if ($extra !== 0){
		$_return['extra'] = $extra;
    }
	echo json_encode($_return);
	exit();
}
$bools = array('false','true');
if (
    ! isset($_POST['email_ntfy'])  ||
    ! isset($_POST['follow']) ||
    ! in_array($_POST['email_ntfy'],$bools) ||
    ! in_array($_POST['follow'],$bools)){
    return_code(7);
}
if(! is_user_logged_in(  )){
    return_code(6);
}
$notifications = 'no-email';
if ($_POST['email_ntfy'] === 'true'){
    $notifications = 'all';
}
if ($_POST['follow'] === 'false'){
    $notifications = false;
}

$return = collection::add_follow($_POST['collection_id'],get_current_user_id(),$notifications);
if (err::is($return)){
    return_code(8);
}
return_code(1);