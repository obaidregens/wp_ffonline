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

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	//Stats
	//Landing
	dbDelta("CREATE TABLE stats_landings (
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
		`host` VARCHAR(30) NOT NULL,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	//Actions
	dbDelta("CREATE TABLE stats_actions (
		`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`landing_id` BIGINT UNSIGNED NOT NULL ,
		`timestamp` BIGINT NOT NULL ,
		`type` VARCHAR(50) NOT NULL ,
		`type_id` BIGINT NOT NULL ,
		`stat` VARCHAR(20) NOT NULL,
		PRIMARY KEY (`ID`)
	) $charset_collate;");


	//surveys
	dbDelta("CREATE TABLE surveys (
		`ID` BIGINT NOT NULL AUTO_INCREMENT ,
		`vfs` VARCHAR(100) NOT NULL ,
		`user_id` BIGINT NOT NULL ,
		`timestamp` BIGINT NOT NULL ,
		`type` VARCHAR(50) NOT NULL ,
		`rating` TINYINT NULL ,
		`suggestion` TEXT NULL ,
		`email` VARCHAR(300) NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE collections (
		`ID` BIGINT NOT NULL AUTO_INCREMENT ,
		`title` VARCHAR(100) NOT NULL ,
		`description` VARCHAR(500) NOT NULL ,
		`type` VARCHAR(20) NOT NULL ,
		`slug` VARCHAR(100) NULL DEFAULT NULL ,
		`created` BIGINT NOT NULL ,
		`modified` BIGINT NOT NULL ,
		`author` BIGINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE collection_books (
		`collection_id` BIGINT NOT NULL ,
		`book_id` BIGINT NOT NULL ,
		`time_added` BIGINT NOT NULL ,
		PRIMARY KEY (`collection_id`,`book_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE search_cache (
		`_key` VARCHAR(50) NOT NULL ,
		`_value` VARCHAR(50) NOT NULL ,
		`ids` LONGTEXT NOT NULL ,
		`updated` BIGINT NOT NULL ,
		PRIMARY KEY (`_key`,`_value`)
	) $charset_collate;");

	dbDelta("CREATE TABLE verification_codes (
		`ID` BIGINT NOT NULL AUTO_INCREMENT,
		`code` VARCHAR(8) NOT NULL ,
		`issued` BIGINT NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;");

	dbDelta("CREATE TABLE user_connections (
		`ID` BIGINT NOT NULL AUTO_INCREMENT,
		`user_id` BIGINT NOT NULL,
		`connection_user` VARCHAR(50) NOT NULL ,
		`connection_from` VARCHAR(20) NOT NULL ,
		`status` VARCHAR(20) NOT NULL ,
		`link_timestamp` BIGINT NULL ,
		`unlink_timestamp` BIGINT NULL ,
		`verification_ID` BIGINT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;");

	dbDelta("CREATE TABLE drafts (
		`ID` BIGINT NOT NULL AUTO_INCREMENT,
		`user_id` BIGINT NOT NULL,
		`share` VARCHAR(25) NULL ,
		`title` VARCHAR(100) NOT NULL ,
		`chapter_id` BIGINT NULL ,
		`created` BIGINT NOT NULL ,
		`branch_type` VARCHAR(20) NULL ,
		`path` TEXT NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;");
	dbDelta("CREATE TABLE draft_revisions (
		`ID` BIGINT NOT NULL AUTO_INCREMENT ,
		`user_id` BIGINT NOT NULL ,
		`draft_id` BIGINT NOT NULL ,
		`content` LONGTEXT NOT NULL ,
		`hash` VARCHAR(40) NOT NULL ,
		`edited` BIGINT NOT NULL ,
		PRIMARY KEY (ID)
	) $charset_collate;");

	dbDelta("CREATE TABLE import_stories (
		`import_user` 	VARCHAR(50) NOT NULL ,
		`import_from` 	VARCHAR(20) NOT NULL ,
		`user_id` 		BIGINT NOT NULL ,
		`story_id`	  	BIGINT NOT NULL ,
		`import_story`	BIGINT NOT NULL ,
		`import_status`	VARCHAR(20) NOT NULL ,
		`request_time`	BIGINT NOT NULL ,
		`import_time`	BIGINT NOT NULL ,
		`import_favs`	BIGINT NOT NULL ,
		`import_follows`BIGINT NOT NULL ,
		`viewed_time`	BIGINT NOT NULL ,
		PRIMARY KEY (`import_from`,`import_story`)
	) $charset_collate;");

	dbDelta("CREATE TABLE ffn_outreach (
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
	) $charset_collate;");

	dbDelta("CREATE TABLE character_pairings (
		`pairing_id` 	BIGINT NOT NULL ,
		`character_id` 	BIGINT NOT NULL ,
		PRIMARY KEY (`pairing_id`,`character_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE pairing_relationships (
		`pairing_id` 	BIGINT NOT NULL ,
		`book_id`		BIGINT NOT NULL ,
		`priority`		VARCHAR(20) NOT NULL ,
		`added_time`	BIGINT NOT NULL ,
		PRIMARY KEY (`pairing_id`,`book_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE contact (
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
	) $charset_collate;");

	// Follows/Notifications
	dbDelta("CREATE TABLE follows (
		`type` 			VARCHAR(50) NOT NULL ,
		`type_id`		BIGINT NOT NULL ,
		`user_id`		BIGINT NOT NULL ,
		`notifications`	VARCHAR(20) NOT NULL ,
		`landing_id`	BIGINT NOT NULL ,
		`followed_time`	DOUBLE NOT NULL ,
		PRIMARY KEY (`type`,`type_id`,`user_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE votes (
		`type` 			VARCHAR(50) NOT NULL ,
		`type_id`		BIGINT NOT NULL ,
		`user_id`		BIGINT NOT NULL ,
		`landing_id`	BIGINT NOT NULL ,
		`voted_time`	DOUBLE NOT NULL ,
		PRIMARY KEY (`type`,`type_id`,`user_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE notifications (
		`ID` 				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`user_id`			BIGINT NOT NULL ,
		`notification_type` VARCHAR(100) NOT NULL ,
		`type_of`			VARCHAR(70) NOT NULL ,
		`type_of_id`		BIGINT NOT NULL ,
		`type_by`			VARCHAR(70) NOT NULL ,
		`type_by_id`		BIGINT NOT NULL ,
		`email_status`		VARCHAR(120) NOT NULL ,
		`timestamp`			DOUBLE NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE chats (
		`ID` 				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`from`				BIGINT NOT NULL ,
		`to`				BIGINT NOT NULL ,
		`status`			VARCHAR(20) NOT NULL ,
		`message`			VARCHAR(400) NOT NULL ,
		`milli_timestamp`	BIGINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE questions (
		`ID` 				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`email`				VARCHAR(300) NOT NULL ,
		`user_id`			BIGINT NOT NULL ,
		`for_user`			BIGINT NOT NULL ,
		`status`			VARCHAR(20) NOT NULL,
		`category`			VARCHAR(200) NOT NULL,
		`question`			TEXT NOT NULL ,
		`answer`			TEXT NOT NULL ,
		`landing_id`		BIGINT NOT NULL ,
		`asked_millitime`	BIGINT UNSIGNED NOT NULL ,
		`replied_millitime`	BIGINT UNSIGNED NOT NULL ,
		`deleted_millitime`	BIGINT UNSIGNED NOT NULL ,
		`is_anonymous`		TINYINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE polls (
		`ID` 				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`user_id`			BIGINT NOT NULL ,
		`description`		TEXT NOT NULL ,
		`status`			VARCHAR(20) NOT NULL ,
		`created_milli`		BIGINT NOT NULL ,
		`deleted_milli`		BIGINT NOT NULL ,
		`expire_in`			BIGINT NOT NULL,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE poll_options (
		`ID`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`poll_id`			BIGINT UNSIGNED NOT NULL ,
		`title`				VARCHAR(50) NOT NULL ,
		UNIQUE (`poll_id`,`title`) ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE poll_votes (
		`poll_id`			BIGINT UNSIGNED NOT NULL ,
		`option_id`			BIGINT NOT NULL ,
		`user_id`			BIGINT NOT NULL ,
		`voted_millitime`	BIGINT NOT NULL ,
		PRIMARY KEY (`poll_id`,`user_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE offline_stats (
		`ID`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`type`				VARCHAR(20) NOT NULL ,
		`key`				VARCHAR(100) NOT NULL ,
		`landing_id`		BIGINT NOT NULL ,
		`story_id`			BIGINT NOT NULL ,
		`chapter_id`		BIGINT NOT NULL ,
		`chapter_num`		BIGINT NOT NULL ,
		`stat_millitime`	BIGINT NOT NULL ,
		`added_millitime`	BIGINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE spam_log (
		`ID`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`reason`			VARCHAR(100) NOT NULL ,
		`user_id`			BIGINT NOT NULL ,
		`IP`				VARCHAR(100) NOT NULL ,
		`landing_id`		BIGINT NOT NULL ,
		`description`		TEXT NOT NULL ,
		`logged_millitime`	BIGINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE dictionary_data (
		`word`				VARCHAR(150) NOT NULL ,
		`google`			LONGTEXT NOT NULL ,
		`thesaurus_com`		LONGTEXT NOT NULL ,
		`landing_id`		BIGINT NOT NULL ,
		`milli_timestamp`	BIGINT NOT NULL ,
		PRIMARY KEY (`word`)
	) $charset_collate;");

	dbDelta("CREATE TABLE beta_sessions (
		`ID`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`description`		VARCHAR(500) NOT NULL ,
		`start_url`			VARCHAR(100) NOT NULL ,
		`start_time`		BIGINT NOT NULL ,
		`end_time`			BIGINT NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE beta_users (
		`beta_id`			BIGINT UNSIGNED NOT NULL ,
		`user_id`			BIGINT NOT NULL ,
		`selection`			VARCHAR(30) NOT NULL ,
		PRIMARY KEY (`beta_ID`,`user_id`)
	) $charset_collate;");

	dbDelta("CREATE TABLE reviews (
		`ID`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`type`				VARCHAR(50) NOT NULL ,
		`type_id`			BIGINT UNSIGNED NOT NULL ,
		`review`			TEXT NOT NULL,
		`user_id`			BIGINT NOT NULL ,
		`landing_id`		BIGINT UNSIGNED NOT NULL ,
		`reply`				BIGINT UNSIGNED NOT NULL ,
		`status`			VARCHAR(50) NOT NULL ,
		`millitime`			BIGINT UNSIGNED NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");

	dbDelta("CREATE TABLE google_auth (
		`ID`				BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
		`user_id`			BIGINT NOT NULL ,
		`email`				VARCHAR(350) NOT NULL ,
		`google_user_id`	VARCHAR(50) NOT NULL,
		`landing_id`		BIGINT UNSIGNED NOT NULL ,
		`registered`		BIGINT UNSIGNED NOT NULL ,
		PRIMARY KEY (`ID`)
	) $charset_collate;");


	//Create Default Collections for users
	$users = get_users(array(
		'fields'	=> array('ID')
	));
	foreach ($users as $user ) {
		collection_helpers::create_default($user->ID);
	}
}
register_activation_hook(__FILE__, 'run_at_activation' );

$includes = array(
	'misc',
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
	'classes/questions',
	'classes/poll',
	'classes/offline_stats',
	'classes/spam',
	'classes/db',
	'classes/story',
	'classes/beta',
);
foreach($includes as $include){
	require ($include . '.php');
}