<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['email']) ){
	foreach($_POST as $value){
		$value = strip_tags($value,array('<p>','<br>','<strong>','<em>','<u>','<i>'));
	}
    if ($_POST['email'] == get_the_author_meta('user_email',get_current_user_id())){
        echo '1';
    }
    else if (email_exists($_POST['email']) == false)
    {
        echo '2';
    }
    else{
        echo '0';
    }
 exit;
}
