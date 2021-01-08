<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

$sql =
"UPDATE track_reading
INNER JOIN wp_postmeta ON track_reading.chapter_id = wp_postmeta.post_id
SET track_reading.chapter_num = wp_postmeta.meta_value
WHERE wp_postmeta.meta_key = 'chapter_order'";

global $wpdb;
$rows_affected = $wpdb->query($sql);

echo "Rows Updated: $rows_affected";