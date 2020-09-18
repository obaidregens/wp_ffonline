<?php
define('WP_USE_THEMES', false);
require("../wp/wp-load.php");

$cache = new book_query_cache;
$cache->words();
$cache->tax();
$cache->sort();
$cache->pairings();
$cache->author();
$cache->tag_names();
$cache->pairing_tag_names();
$cache->ffn_author();