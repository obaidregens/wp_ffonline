<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

global $wpdb;
$r = array_column($wpdb->get_results(
    "SELECT ID FROM reviews WHERE ID NOT IN (
        SELECT `type_of_id`
        FROM notifications
        WHERE notification_type IN('chapter_review','review_reply')
    )"
),'ID');
$ni = new notifications_insert;
foreach ($r as $review_id ) {
    $ni->addReview($review_id);
}