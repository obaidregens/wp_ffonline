<?php
define('WP_USE_THEMES', false);
require("../wp/wp-load.php");

$cache = new book_query_cache;
$cache->words();
$cache->tax();
$cache->sort();
$cache->author();
$cache->tag_names();