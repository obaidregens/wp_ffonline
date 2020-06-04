<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');


if (
    (! isset($_POST['rating']) || ! $_POST['rating']) &&
    ( ! isset($_POST['suggestion'])  || ! $_POST['suggestion'])
){
    echo json_encode(array(
        'code'  => 7,
    ));
    exit();
}

$max_rating = 5;

$vfs = _landing::vfs();
global $wpdb;
$wpdb->insert(
    'surveys', 
    array(
        'vfs' => $vfs,
        'user_id' => get_current_user_id(),
        'timestamp' => current_time('timestamp',true),
        'type'      => 'general',
        'rating' => isset($_POST['rating']) ? ($_POST['rating']/$max_rating)*100 : null,
        'suggestion' => isset($_POST['suggestion']) && $_POST['suggestion'] ? $_POST['suggestion'] : null,
        'email' => isset($_POST['email']) && $_POST['email'] ? $_POST['email'] : null,
    )
);
$survey_id = $wpdb->insert_id;
echo json_encode(array(
    'code'  => 1,
));
exit();