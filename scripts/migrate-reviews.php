<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

global $wpdb;
$r = $wpdb->get_results("SELECT * FROM wp_comments");
foreach ($r as $v) {
    $a = $wpdb->get_results($wpdb->prepare(
        "SELECT ID FROM stats_landings
        WHERE `IP` = %s
        AND `type` = 'chapter'
        AND `type_id` = %d",
        [$v->comment_author_IP,$v->comment_post_ID]
    ))[0]->ID ?? 0;
    $wpdb->insert(
        'reviews',
        [
            'ID'        => $v->comment_ID,
            'type'      => 'chapter',
            'type_id'   => $v->comment_post_ID,
            'review'    => $v->comment_content,
            'user_id'   => $v->user_id,
            'landing_id'=> $a,
            'reply'     => $v->comment_parent,
            'status'    => $v->comment_approved === "trash" ? "trash" : "public",
            'millitime' => strtotime($v->comment_date)*1000
        ]
    );
}