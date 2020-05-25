<?php
/**
 * Plugin Name: 1-Book Writer
 * Description: A plugin by Fanfiction Online, for Fanfiction Online.
 * Version: 1.0
 * Author: Fanfiction Online
 * Author URI: https://www.fanfiction.online
 */
function run_at_activation(){
	//Create Pages
	//Privacy Policy
	$page_db = (new WP_Query(array(
		'title'			=> 'Privacy Policy',
		'post_type'		=> 'page',
		'posts_per_page'	=> 1,
	)))->posts;
	if (empty($page_db)){
		wp_insert_post( array(
			'post_title' => 'Privacy Policy',
			'post_type'     => 'page',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_status' => 'draft',
			'post_content' => '<h2>We are https://fanfiction.online.</h2><h2>What personal data we collect and why we collect it</h2><p>We collect data about your devices (e.g. computers, browsers) and other data that you provide us with (e.g. your name, your email). This information may include your IP address, time of your visit, and <a href="#cookies">cookies</a>. We use this information to improve and protect https://fanfiction.online.</p><h3 id="cookies">Cookies</h3><p>If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.</p><p>When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two weeks, and screen options cookies last for a year.  If you log out of your account, the login cookies will be removed.</p><p>Whenever you customize your reading experience (e.g. adjusting font size), cookies are set up to save these customizations.  These cookies last for 30 days.</p><h2>Who we share your data with</h2><h3>Google Services</h3><p>We use multiple Google services like Google Analytics, Google reCAPTCHA, and others to improve and safeguard https://fanfiction.online. For more information on Google’s privacy policy, please visit <a href="https://policies.google.com/privacy">https://policies.google.com/privacy</a>.</p><h2>How long we retain your data</h2><p>For authors that signup on our website, we store the personal information they provide in their profile. All authors can see, edit, or delete their personal information at any time with the exception of usernames, which cannot be changed.</p><h2>What rights you have over your data</h2><p>If you have an account on this site,  you can request to receive personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you.</p><h2>Your contact information</h2><p>You can email us through our <a href="https://fanfiction.online/contact/">contact form</a>.</p><h2>Security of Data</h2><p>We use standard encryption, and other security measures to ensure data remains safe and protected.</p>',
			'post_status' => 'publish',
		));	
	}
	//Other Pages
	$pages = array(
		array('Add a fandom','fandom','page-create-cat.php'),
		array('Collections','collection','page-collection.php'),
		array('Contact','contact','page-contact.php'),
		array('Dashboard','dashboard','page-dashboard.php'),
		array('Login','login','page-login.php'),
		array('Pick a New Password','reset-password','page-reset-password.php'),
		array('Read','read','page-search.php'),
		array('Manage','manage','manage/index.php')
	);
	foreach($pages as $page){
		$page_db = (new WP_Query(array(
			'name'			=> $page[1],
			'post_type'		=> 'page',
			'posts_per_page'	=> -1,
		)))->posts;
		if (empty($page_db)){
			$page_id = wp_insert_post( array(
				'post_title' => $page[0],
				'post_type'     => 'page',
				'post_name'		=> $page[1],
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_content' => '',
				'post_status' => 'publish',
			));
		}
		else{
			$page_id = $page_db[0]->ID;
		}
		update_post_meta($page_id,'_wp_page_template',$page[2]);
		//Set Front Page
		if ($page[0] == 'Read'){
			update_option('page_on_front',$page_id);
			update_option( 'show_on_front', 'page' );
		}
	}
	//Remove Widgets
	update_option('sidebars_widgets',array());
	update_option('acme_cleared_widget',array());
	update_option('default_role','author');
	//Activate Theme
	switch_theme('book-writer');
	flush_rewrite_rules();


	//Add Searches Table
	global $wpdb;
	$searches_table_name = 'searches';
	$searchparams_table_name = 'searchparams';
	$charset_collate = $wpdb->get_charset_collate();

	$searches_table = "CREATE TABLE $searches_table_name (
		  `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		  `user_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' ,
		  `type` VARCHAR(20) NOT NULL DEFAULT 'main' ,
		  `type_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' ,
		  `timestamp` BIGINT UNSIGNED NOT NULL ,
		  `IP` VARCHAR(100) NOT NULL ,
		  `args` LONGTEXT NOT NULL ,
		  PRIMARY KEY (`ID`),
		  KEY user_id (user_id),
		  KEY type (type),
		  KEY type_id (type_id),
		  KEY IP (IP),
		) $charset_collate;";

	$searchparams_table = "CREATE TABLE $searchparams_table_name (
		  `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		  `search_id` BIGINT UNSIGNED NOT NULL ,
		  `parameter` VARCHAR(500) NOT NULL ,
		  `value` VARCHAR(500) NOT NULL ,
		  PRIMARY KEY (`ID`),
		  KEY search_id (search_id),
		  KEY parameter (parameter),
		  KEY value (value),
		) $charset_collate;";
    

	//Add Stats Table
	$custom_stats_table_name = 'custom_stats';
	$custom_stats_table = "CREATE TABLE $custom_stats_table_name (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`cookie_id` VARCHAR(100) NOT NULL ,
	`timestamp` BIGINT NOT NULL ,
	`stat` VARCHAR(50) NOT NULL ,
	`type` VARCHAR(50) NOT NULL ,
	`type_id` BIGINT NOT NULL ,
	`user_id` BIGINT NOT NULL ,
	`IP` VARCHAR(100) NOT NULL ,
	`referrer_host` VARCHAR(150) NULL ,
	`referrer_path` VARCHAR(300) NULL ,
	PRIMARY KEY (`ID`)
	) $charset_collate;";
	
	//Autosaves table
	$custom_autosaves_table_name = 'custom_autosaves';
	$custom_autosaves_table = "CREATE TABLE $custom_autosaves_table_name (
	`ID` BIGINT NOT NULL AUTO_INCREMENT,
	`book_id` BIGINT NOT NULL,
	`chapter_id` BIGINT NOT NULL,
	`author` BIGINT NOT NULL,
	`timestamp` BIGINT NOT NULL,
	`content` LONGTEXT NOT NULL,
	PRIMARY KEY (`ID`)
	) $charset_collate;";

    //RUN SQL
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $searches_table );
	dbDelta( $searchparams_table );
	dbDelta( $custom_stats_table );
	dbDelta( $custom_autosaves_table );

}
register_activation_hook(__FILE__, 'run_at_activation' );

$includes = array(
	'endpoints',
	'misc',
	'chapter-navigator',
	'cpt',
	'chapter-fields',
	'custom-login',
	'searches',
	'custom_cache',
	'data',
	'validation',
	'stats',
	'bundles',
	'privileges'
);
foreach($includes as $include){
	include ($include . '.php');
}