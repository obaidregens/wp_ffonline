<?php
exit();
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');

global $wpdb;
$r = $wpdb->get_results("SELECT * FROM `wp_comments`");
foreach ($r as $k => $review) {
    $chapter = get_post($review->comment_post_ID);
    if (! $chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish'){
        continue;
    }
    $wpdb->insert(
        'notifications',
        [
            'notification_type' => 'chapter_review',
            'type_of'           => 'review',
            'type_of_id'        => $review->comment_ID,
            'type_by'           => 'chapter',
            'type_by_id'        => $chapter->ID,
            'user_id'           => $chapter->post_author,
            'email_status'      => 'none',
            'timestamp'         => strtotime($review->comment_date),
        ]
    );
}