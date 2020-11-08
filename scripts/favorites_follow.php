<?php
exit();
$a = collection::query([
    'types' => ['Favorites']
]);
foreach ($a as $collection) {
    $e = collection_follow::follow($collection->ID, $collection->author, 0);
}