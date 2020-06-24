<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
$cache = new book_query_cache;
$cache->words();
$cache->tax();
$cache->sort();
$cache->author();