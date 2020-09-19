<?php
$start = time();
set_time_limit(0);
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');

bundle::reWrite();

echo "Script Ended in: " . (time() - $start) . "seconds \n";