<?php
function is_current_user($user_id) {
	return intval($user_id) === intval(get_current_user_id());
}
/**
 * Plugin Name: 1-Book Writer
 * Description: A plugin by Fanfiction Online, for Fanfiction Online.
 * Version: 1.0
 * Author: Fanfiction Online
 * Author URI: https://www.fanfiction.online
 */

function run_at_activation(){
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
	$stats_landings_table = "CREATE TABLE stats_landings (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`vfs` VARCHAR(100) NOT NULL ,
	`timestamp` BIGINT NOT NULL ,
	`type` VARCHAR(50) NOT NULL ,
	`type_id` BIGINT NOT NULL ,
	`request` TEXT NULL ,
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
	$stats_actions_table = "CREATE TABLE stats_actions (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
	`landing_id` BIGINT UNSIGNED NOT NULL ,
	`timestamp` BIGINT NOT NULL ,
	`type` VARCHAR(50) NOT NULL ,
	`type_id` BIGINT NOT NULL ,
	`stat` VARCHAR(20) NOT NULL,
	PRIMARY KEY (`ID`)
	) $charset_collate;";


	//surveys
	$surveys_table = "CREATE TABLE surveys (
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

	$collections_table = "CREATE TABLE collections (
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

	$collection_books_table = "CREATE TABLE collection_books (
		`collection_id` BIGINT NOT NULL ,
		`book_id` BIGINT NOT NULL ,
		`time_added` BIGINT NOT NULL ,
		PRIMARY KEY (`collection_id`,`book_id`)
	) $charset_collate;";

	$search_cache_table = "CREATE TABLE search_cache (
		`_key` VARCHAR(50) NOT NULL ,
		`_value` VARCHAR(50) NOT NULL ,
		`ids` LONGTEXT NOT NULL ,
		`updated` BIGINT NOT NULL ,
		PRIMARY KEY (`_key`,`_value`)
	) $charset_collate;";

	$verification_codes_table = "CREATE TABLE verification_codes (
		`ID` BIGINT NOT NULL AUTO_INCREMENT,
		`code` VARCHAR(8) NOT NULL ,
		`issued` BIGINT NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;";

	$user_connections_table = "CREATE TABLE user_connections (
		`ID` BIGINT NOT NULL AUTO_INCREMENT,
		`user_id` BIGINT NOT NULL,
		`connection_user` VARCHAR(50) NOT NULL ,
		`connection_from` VARCHAR(20) NOT NULL ,
		`status` VARCHAR(20) NOT NULL ,
		`link_timestamp` BIGINT NULL ,
		`unlink_timestamp` BIGINT NULL ,
		`verification_ID` BIGINT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;";

	$drafts_table = "CREATE TABLE drafts (
		`ID` BIGINT NOT NULL AUTO_INCREMENT,
		`user_id` BIGINT NOT NULL,
		`share` VARCHAR(25) NULL ,
		`title` VARCHAR(100) NOT NULL ,
		`chapter_id` BIGINT NULL ,
		`created` BIGINT NOT NULL ,
		`branch_type` VARCHAR(20) NULL ,
		`path` TEXT NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;";
	$draft_revisions_table = "CREATE TABLE draft_revisions (
		`ID` BIGINT NOT NULL AUTO_INCREMENT ,
		`user_id` BIGINT NOT NULL ,
		`draft_id` BIGINT NOT NULL ,
		`content` LONGTEXT NOT NULL ,
		`hash` VARCHAR(40) NOT NULL ,
		`edited` BIGINT NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;";

	$import_stories_table = "CREATE TABLE import_stories (
		`import_user` 	VARCHAR(50) NOT NULL ,
		`import_from` 	VARCHAR(20) NOT NULL ,
		`user_id` 		BIGINT NOT NULL ,
		`story_id`	  	BIGINT NOT NULL ,
		`import_story`	BIGINT NOT NULL ,
		`import_status`	VARCHAR(20) NOT NULL ,
		`request_time`	BIGINT NOT NULL ,
		`import_time`	BIGINT NOT NULL ,
		`viewed_time`	BIGINT NOT NULL ,
		PRIMARY KEY (`import_from`,`import_story`)
	) $charset_collate;";

	$ffn_outreach_table = "CREATE TABLE ffn_outreach (
		`ffn_user_id`			BIGINT NOT NULL ,
		`message_sent`			BIGINT NOT NULL ,
		`ffn_username`			VARCHAR(40) NOT NULL ,
		`ffn_joined`			BIGINT NOT NULL ,
		`profile_updated`		BIGINT NOT NULL ,
		`total_stories`			INT NOT NULL ,
		`most_fandom`			VARCHAR(200) NOT NULL ,
		`total_chapters`		INT NOT NULL ,
		`total_words`			BIGINT NOT NULL ,
		`total_favs`			BIGINT NOT NULL ,
		`total_follows`			BIGINT NOT NULL ,
		`total_reviews`			BIGINT NOT NULL ,
		`total_rating`			INT NOT NULL ,
		`top_favs_fandom`		VARCHAR(200) NOT NULL ,
		`top_favs_words`		BIGINT NOT NULL ,
		`top_favs_reviews`		INT NOT NULL ,
		`top_favs_chapters`		INT NOT NULL ,
		`top_favs_favs`			INT NOT NULL ,
		`top_favs_follows`		INT NOT NULL ,
		`top_favs_rating`		VARCHAR(5) NOT NULL ,
		`top_favs_language`		VARCHAR(100) NOT NULL ,
		`top_favs_genre`		VARCHAR(200) NOT NULL ,
		`top_favs_updated`		BIGINT NOT NULL ,
		`top_favs_published`	BIGINT NOT NULL ,
		`oldest_published`		BIGINT NOT NULL ,
		`oldest_updated`		BIGINT NOT NULL ,
		`newest_published`		BIGINT NOT NULL ,
		`newest_updated`		BIGINT NOT NULL ,
		PRIMARY KEY (`ffn_user_id`)
	) $charset_collate;";

	$character_pairings_tables = "CREATE TABLE character_pairings (
		`pairing_id` 	BIGINT NOT NULL ,
		`character_id` 	BIGINT NOT NULL ,
		PRIMARY KEY (`pairing_id`,`character_id`)
	) $charset_collate;";

	$pairing_relationships_tables = "CREATE TABLE pairing_relationships (
		`pairing_id` 	BIGINT NOT NULL ,
		`book_id`		BIGINT NOT NULL ,
		`priority`		VARCHAR(20) NOT NULL ,
		`added_time`	BIGINT NOT NULL ,
		PRIMARY KEY (`pairing_id`,`book_id`)
	) $charset_collate;";

	$contact_tables = "CREATE TABLE contact (
		`ID` 			BIGINT NOT NULL AUTO_INCREMENT ,
		`user_id`		BIGINT NOT NULL ,
		`from`		 	VARCHAR(400) NOT NULL ,
		`to`			VARCHAR(200) NOT NULL ,
		`received_time`	DOUBLE NOT NULL ,
		`subject`		VARCHAR(200) NOT NULL ,
		`headers`		LONGTEXT NOT NULL ,
		`message`		LONGTEXT NOT NULL ,
		`message_id`	VARCHAR(300) NOT NULL ,
		`vfs`			VARCHAR(100) NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;";

	// Follows/Notifications
	$follow_tables = "CREATE TABLE follows (
		`type` 			VARCHAR(50) NOT NULL ,
		`type_id`		BIGINT NOT NULL ,
		`user_id`		BIGINT NOT NULL ,
		`notifications`	VARCHAR(20) NOT NULL ,
		`landing_id`	BIGINT NOT NULL ,
		`followed_time`	DOUBLE NOT NULL ,
		PRIMARY KEY (`type`,`type_id`,`user_id`)
	) $charset_collate;";

	$votes_tables = "CREATE TABLE votes (
		`type` 			VARCHAR(50) NOT NULL ,
		`type_id`		BIGINT NOT NULL ,
		`user_id`		BIGINT NOT NULL ,
		`landing_id`	BIGINT NOT NULL ,
		`voted_time`	DOUBLE NOT NULL ,
		PRIMARY KEY (`type`,`type_id`,`user_id`)
	) $charset_collate;";

    //RUN SQL
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	// ob_start();

	// Stats
	dbDelta( $stats_landings_table );
	dbDelta( $stats_actions_table );
	// Surveys
	dbDelta( $surveys_table );
	// Collections
	dbDelta( $collections_table );
	dbDelta( $collection_books_table );
	// Cache
	dbDelta( $search_cache_table );
	// Codes
	dbDelta( $verification_codes_table );
	// User Connections
	dbDelta( $user_connections_table );
	// Drafts
	dbDelta( $drafts_table );
	dbDelta( $draft_revisions_table );
	// Import Stories
	dbDelta( $import_stories_table );
	// FFN Outreach
	dbDelta( $ffn_outreach_table );
	// Pairings
	dbDelta( $character_pairings_tables );
	dbDelta( $pairing_relationships_tables );
	// Contact Table
	dbDelta( $contact_tables );
	// Follows/Notifications
	dbDelta( $follow_tables );
	dbDelta( $votes_tables );

	//Create Default Collections for users
	$users = get_users(array(
		'fields'	=> array('ID')
	));
	foreach ($users as $user ) {
		collection_helpers::create_default($user->ID);
	}
	// file_put_contents( __DIR__ . '/this.err',ob_get_contents() );
	// ob_end_clean();
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
	'classes/reviews',
	'classes/book_query',
	'classes/user',
	'classes/v_code',
	'classes/c_user',
	'classes/drafts',
	'classes/book_stats',
	'classes/dict',
	'classes/updates',
	'classes/import_stories',
	'classes/tags',
	'classes/follow',
	'classes/vote',
);
foreach($includes as $include){
	require ($include . '.php');
}