<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax']) && isset($_POST['email'])) {
	//Validations -> Fields (email,^about,display_name,password);
	if ($_POST['email'] == '' || $_POST['display_name'] == ''){
		echo '6';
		exit();
	}
	if (! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
		echo '6';
		exit();
	}
	if (strlen($_POST['password']) < 8 && $_POST['password'] != ''){
		echo '6';
		exit();
	}
	$new_email = $_POST['email'];
	send_confirmation_on_profile_email();

	$userdata = array(
	    'ID'            => get_current_user_id(),
	    'nickname'      => $_POST['display_name'],
	    'user_nicename' => $_POST['display_name'],
	    'description'   => htmlspecialchars($_POST['about']),

    );
    if ($_POST['password'] != ''){
        $userdata['user_pass'] = $_POST['password'];
    }
	wp_update_user($userdata);
	if ($new_email != get_the_author_meta('user_email',get_current_user_id())){
	    echo '1';
	}
	else{
	    delete_user_meta( $current_user->ID, '_new_email' );
	    echo '2';
	}
	exit();
}