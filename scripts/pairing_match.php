<?php
exit();
define("NO_ROUTES",true);
require (rtrim(explode('content',__DIR__,2)[0],'/\\') . '/content/index.php');

global $wpdb;
$r = $wpdb->get_results("SELECT * FROM character_pairings ORDER BY character_id DESC");

$b =[];
foreach ($r as $a) {
    $k = &$b[$a->pairing_id];
    $k = $k ?? [];
    $k[] = $a->character_id;
}
$new = [];
$to_remove = [];
foreach ($b as $pairing_id => $chars) {
    $existing = array_search($chars,array_values($new));
    if ($existing === false) {
        $new[$pairing_id] = $chars;
        continue;
    }
    $to_remove[] = [
        'remove'    => $pairing_id,
        'merge'     => $existing
    ];
}
$f = MAIN_DIR . '/pairings.txt';
file_put_contents($f,json_encode( $to_remove , JSON_PRETTY_PRINT ) );
// $dat = file_get_contents($f);
// $to_remove = json_decode($dat,true);
foreach ($to_remove as $removal) {
    $updated = $wpdb->update(
        'pairing_relationships',
        [
            'pairing_id'    => $removal['merge']
        ],
        [
            'pairing_id'    => $removal['remove']
        ]
    );
    $wpdb->delete(
        'character_pairings',
        [
            'pairing_id'    => $removal['remove']
        ]
    );
}