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
	update_option('show_avatars',0);
	update_option('sidebars_widgets',array());
	update_option('acme_cleared_widget',array());
	update_option('default_role','author');
	//Activate Theme
	switch_theme('book-writer');
	flush_rewrite_rules();
    
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	//Stats
	//Landing
	$stats_landings_table_name = 'stats_landings';
	$stats_landings_table = "CREATE TABLE $stats_landings_table_name (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`vfs` VARCHAR(100) NOT NULL ,
	`timestamp` BIGINT NOT NULL ,
	`type` VARCHAR(50) NOT NULL ,
	`type_id` BIGINT NOT NULL ,
	`user_id` BIGINT NOT NULL ,
	`IP` VARCHAR(100) NOT NULL ,
	`referrer_host` VARCHAR(150) NULL ,
	`referrer_path` VARCHAR(300) NULL ,
	`platform` VARCHAR(100) NULL,
	`browser` VARCHAR(100) NULL,
	`browser_version` VARCHAR(20) NULL,
	PRIMARY KEY (`ID`)
	) $charset_collate;";
	//Actions
	$stats_actions_table_name = 'stats_actions';
	$stats_actions_table = "CREATE TABLE $stats_actions_table_name (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`landing_id` BIGINT UNSIGNED NOT NULL ,
	`timestamp` BIGINT NOT NULL ,
	`type` VARCHAR(50) NOT NULL ,
	`type_id` BIGINT NOT NULL ,
	`stat` VARCHAR(20) NOT NULL,
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


	//surveys
	$surveys_table_name = 'surveys';
	$surveys_table = "CREATE TABLE $surveys_table_name (
		`ID` BIGINT NOT NULL AUTO_INCREMENT ,
		`vfs` VARCHAR(100) NOT NULL ,
		`user_id` BIGINT NOT NULL ,
		`timestamp` BIGINT NOT NULL ,
		`type` VARCHAR(50) NOT NULL ,
		`rating` TINYINT NULL ,
		`suggestion` TEXT NULL ,
		`email` VARCHAR(300) NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;";

	$collections_table_name = 'collections';
	$collections_table = "CREATE TABLE $collections_table_name (
		`ID` BIGINT NOT NULL AUTO_INCREMENT ,
		`title` VARCHAR(100) NOT NULL ,
		`description` VARCHAR(500) NOT NULL ,
		`type` VARCHAR(20) NOT NULL ,
		`slug` VARCHAR(100) NULL DEFAULT NULL ,
		`created` BIGINT NOT NULL ,
		`modified` BIGINT NOT NULL ,
		`author` BIGINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;";

	$collection_books_table_name = 'collection_books';
	$collection_books_table = "CREATE TABLE $collection_books_table_name (
		`collection_id` BIGINT NOT NULL ,
		`book_id` BIGINT NOT NULL ,
		`time_added` BIGINT NOT NULL ,
		PRIMARY KEY (`collection_id`,`book_id`)
	) $charset_collate;";

	$collection_follow_table_name = 'collection_follow';
	$collection_follow_table = "CREATE TABLE $collection_follow_table_name (
		`collection_id` BIGINT NOT NULL ,
		`user_id` BIGINT NOT NULL ,
		`notifications` VARCHAR(20) NOT NULL ,
		`time_followed` BIGINT NOT NULL ,
		PRIMARY KEY (`collection_id`,`user_id`)
	) $charset_collate;";

	$search_cache_table_name = 'search_cache';
	$search_cache_table = "CREATE TABLE $search_cache_table_name (
		`_key` VARCHAR(50) NOT NULL ,
		`_value` VARCHAR(50) NOT NULL ,
		`ids` LONGTEXT NOT NULL ,
		`updated` BIGINT NOT NULL ,
		PRIMARY KEY (`_key`,`_value`)
	) $charset_collate;";

    //RUN SQL
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	ob_start();

	// Stats
	dbDelta( $stats_landings_table );
	dbDelta( $stats_actions_table );
	// Autosaves
	dbDelta( $custom_autosaves_table );
	// Surveys
	dbDelta( $surveys_table );
	// Collections
	dbDelta( $collections_table );
	dbDelta( $collection_books_table );
	dbDelta( $collection_follow_table );
	// Cache
	dbDelta( $search_cache_table );


	//Create Default Collections for users
	$users = get_users(array(
		'fields'	=> array('ID')
	));
	foreach ($users as $user ) {
		collection::create_default($user->ID);
	}
	file_put_contents( __DIR__ . '/this.err',ob_get_contents() );
	ob_end_clean();
}
register_activation_hook(__FILE__, 'run_at_activation' );

$includes = array(
	'survey_query',
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
	'classes/bundles',
	'privileges',
	'classes/stats',
	'classes/collections',
	'classes/notifications',
	'classes/error',
	'classes/chats',
	'classes/book_query',
	'classes/v_user'
);
foreach($includes as $include){
	require ($include . '.php');
}