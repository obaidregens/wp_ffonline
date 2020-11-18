<?php
ignore_user_abort(true);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
define("NO_ROUTES",true);
require ($maindir . '/content/index.php');

$sql = "SELECT books.ID as id,SUM(met.meta_value) as c FROM wp_posts as books
INNER JOIN wp_posts as chapters ON books.ID = chapters.post_parent
INNER JOIN wp_postmeta as met ON chapters.ID = met.post_id
WHERE 1
AND books.post_status = 'publish'
AND chapters.post_status = 'publish'
AND books.post_type = 'book'
AND chapters.post_type = 'chapter'
AND met.meta_key = 'word-count'
GROUP BY books.ID
";
global $wpdb;
$r = $wpdb->get_results($sql);
foreach ($r as $row) {
    update_post_meta( intval($row->id), 'word-count', intval($row->c) );
}
echo "Rows: " . count($r);