<?php
/**
 * Plugin Name: 1-Book Writer
 * Description: A plugin by Fanfiction Online, for Fanfiction Online.
 * Version: 1.0
 * Author: Fanfiction Online
 * Author URI: https://www.fanfiction.online
 */
function run_at_activation(){
	$old_pages = get_pages(array('post_status'=>array('publish','draft','trash','future')));
	foreach($old_pages as $page){
		wp_delete_post($page->ID,true);
	}
	//Create Pages
	//Privacy Policy
	wp_insert_post( array(
		'post_title' => 'Privacy Policy',
		'post_type'     => 'page',
		'comment_status' => 'closed',
		'ping_status' => 'closed',
		'post_content' => '<h2>We are https://fanfiction.online.</h2><h2>What personal data we collect and why we collect it</h2><p>We collect data about your devices (e.g. computers, browsers) and other data that you provide us with (e.g. your name, your email). This information may include your IP address, time of your visit, and <a href="#cookies">cookies</a>. We use this information to improve and protect https://fanfiction.online.</p><h3 id="cookies">Cookies</h3><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two weeks, and screen options cookies last for a year.  If you log out of your account, the login cookies will be removed.</p><p>Whenever you customize your reading experience (e.g. adjusting font size), cookies are set up to save these customizations.  These cookies last for 30 days.</p><h2>Who we share your data with</h2><h3>Google Services</h3><p>We use multiple Google services like Google Analytics, Google reCAPTCHA, and others to improve and safeguard https://fanfiction.online. For more information on Google’s privacy policy, please visit <a href="https://policies.google.com/privacy">https://policies.google.com/privacy</a>.</p><h2>How long we retain your data</h2><p>For authors that signup on our website, we store the personal information they provide in their profile. All authors can see, edit, or delete their personal information at any time with the exception of usernames, which cannot be changed.</p><h2>What rights you have over your data</h2><p>If you have an account on this site,  you can request to receive personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you.</p><h2>Your contact information</h2><p>You can email us through our <a href="https://fanfiction.online/contact/">contact form</a>.</p><h2>Security of Data</h2><p>We use standard encryption, and other security measures to ensure data remains safe and protected.</p>',
		'post_status' => 'publish',
	));
	//Other Pages
	$pages = array(
		array('Add a fandom','fandom','page-create-cat.php'),
		array('Collections','collection','page-collection.php'),
		array('Contact','contact','page-contact.php'),
		array('Dashboard','dashboard','page-dashboard.php'),
		array('Login','login','page-login.php'),
		array('Pick a New Password','reset-password','page-reset-password.php'),
		array('Profile','profile','page-profile.php'),
		array('Read','read','page-search.php')
	);
	foreach($pages as $page){
		$page_id = wp_insert_post( array(
			'post_title' => $page[0],
			'post_type'     => 'page',
			'post_name'		=> $page[1],
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_content' => '',
			'post_status' => 'publish',
			'page_template'  => $page[2]
		));
		//Set Front Page
		if ($page[0] == 'Read'){
			update_option('page_on_front',$page_id);
			update_option( 'show_on_front', 'page' );
		}
		//Remove Widgets
		update_option('sidebars_widgets',array());
		update_option('acme_cleared_widget',array());
		update_option('default_role','author');
		//Activate Theme
		switch_theme('book-writer');
		flush_rewrite_rules();
	}
}
register_activation_hook(__FILE__, 'run_at_activation' );

include('book-validation.php');

include('book-fields.php');

include('misc.php');

include('admin-columns.php');

include ('upload-book-menu.php');

include ('chapter-navigator.php');

include ('cpt.php');

include ('wp_dropdown_posts.php');

include('chapter-validation.php');

include('chapter-fields.php');

include('write/edit-book.php');

include('write/new-chapter.php');

include('write/edit-chapter.php');

include('write/order-book.php');

include('custom-login.php');

include('saved-searches.php');

include('endpoints.php');