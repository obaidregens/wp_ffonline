<?php
exit();
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');

global $wpdb;
$r = $wpdb->get_results("SELECT * FROM collection_follow");

foreach ($r as $k => $v) {
    $wpdb->insert(
        'follows',
        [
            'type'          => 'collection',
            'type_id'       => $v->collection_id,
            'user_id'       => $v->user_id,
            'notifications' => $v->notifications === 'all' ? 'all' : 'none',
            'landing_id'    => 0,
            'followed_time' => $v->time_followed
        ]
    );
    $wpdb->delete(
        'collection_follow',
        [
            'ID'    => $v->ID
        ]
    );
}