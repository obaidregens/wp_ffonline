<?php
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

$f = MAIN_DIR . '/content/pairing_relationships.json';
$pairings = json_decode(file_get_contents($f));
$f = MAIN_DIR . '/content/character_pairings.json';
$characters = (file_get_contents($f));

echo $characters;