<?php
exit();
// Users who've followed their own Favorites
// SELECT collections.ID,follows.user_id FROM collections
// INNER JOIN follows ON collections.ID = follows.type_id
// WHERE collections.type = 'Favorites'
// AND follows.user_id = collections.author
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

$a = collection::query([
    'types' => ['Favorites']
]);
foreach ($a as $collection) {
    $e = collection_follow::follow($collection->ID, $collection->author, 0);
}