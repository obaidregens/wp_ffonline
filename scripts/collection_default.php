<?php
exit();
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

global $wpdb;
$r = $wpdb->get_results(
    "SELECT ID FROM wp_users WHERE ID NOT IN (
        SELECT DISTINCT wp_users.ID FROM wp_users
        INNER JOIN collections ON wp_users.ID = collections.author
        WHERE
            (collections.title = 'Hidden' AND collections.type = 'Private')
        OR
            collections.type = 'Favorites'
    )"
);
$r = array_column($r,'ID');

foreach ($r as $ID ) {
    collection_helpers::create_default($ID);
}