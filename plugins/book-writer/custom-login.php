<?php
add_filter( 'retrieve_password_message', 'my_retrieve_password_message', 11, 4 );
function my_retrieve_password_message( $message, $key, $user_login, $user_data ) {
    // Start with the default content.
    $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    $message = __( 'Someone requested a password reset for your account:' ) . "\r\n\r\n";
    $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
    $message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
    $message .= network_site_url( "reset-password/?login=" . rawurlencode( $user_login ) . "&key=$key", 'login' ) . "\r\n";

    /*
     * If the problem persists with this filter, remove
     * the last line above and use the line below by
     * removing "//" (which comments it out) and hard
     * coding the domain to your site, thus avoiding
     * the network_site_url() function.
     */
    // $message .= '<http://yoursite.com/wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user_login ) . ">\r\n";

    // Return the filtered message.
    return $message;

}
function change_welcome_email($wp_new_user_notification_email,$user,$blogname){
	$user_login = get_the_author_meta('user_login',$user->ID);
	$key = get_password_reset_key( $user );
	/* translators: %s: User login. */
	$message  = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
	$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
	$message .= network_site_url( "reset-password/?login=" . rawurlencode( $user_login ) . "&key=$key", 'login' ) . "\r\n\r\n";
	$wp_new_user_notification_email = array(
		'to'      => $user->user_email,
		/* translators: Login details notification email subject. %s: Site title. */
		'subject' => __( '[%s] Welcome to Fanfiction Online!' ),
		'message' => $message,
		'headers' => '',
	);
	return $wp_new_user_notification_email;
}
add_filter( 'wp_new_user_notification_email', 'change_welcome_email',11,3);