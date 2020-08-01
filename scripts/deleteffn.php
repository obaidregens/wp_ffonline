<?php
$start = microtime(true);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
define('WP_USE_THEMES', false);
require($maindir . "wp/wp-load.php");
$csv = $maindir . 'to_delete.csv';
$file_arr = file($csv);
foreach ($file_arr as $key => $file_line ) {
    wp_delete_post( intval(trim($file_line,'"')), true );
    unset($file_arr[$key]);
    file_put_contents($csv,implode("",$file_arr));
    if (microtime(true) - $start >= 110){
        exit();
    }
}