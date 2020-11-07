<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

global $wpdb;
$r = array_column($wpdb->get_results(
    "SELECT p.post_parent as s,COUNT(p.ID)-COUNT(DISTINCT(m.meta_value)) as c FROM wp_posts as p
    INNER JOIN wp_postmeta as m ON p.ID = m.post_id
    WHERE p.post_type = 'chapter'
    AND m.meta_key = 'chapter_order'
    AND c > 0
    GROUP BY p.post_parent"
),'s');
foreach ( $r as $story ) {
    $chapters = published_chapters($story,-1,'ids');
    foreach ($chapters as $k => $chapter) {
        update_post_meta( $chapter, 'chapter_order', $k+1 );
    }
}