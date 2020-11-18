<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

$cache = new book_query_cache;
$cache->words();
$cache->tax();
$cache->sort();
$cache->pairings();
$cache->author();
$cache->tag_names();
$cache->ffn_author();
$cache->pairing_tag_names();