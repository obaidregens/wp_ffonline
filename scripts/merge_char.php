<?php
exit();
ignore_user_abort(true);

define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
require( $maindir . "/content/wp/wp-load.php");

$merge_from = [17131];
$merge_to_id = 17127;
// Replace in Pairings Table (character_pairings)
// Replace in Stories (wp_term_relationships)
// Delete Term
$p = array_merge([$merge_to_id],$merge_from);
global $wpdb;
$update1 = $wpdb->prepare("UPDATE `character_pairings` SET character_id = %s WHERE character_id IN(" . sqlPlaceholder($merge_from,'%d') . ")",$p);
$r1 = $wpdb->query($update1);
$update2 = $wpdb->prepare("UPDATE `wp_term_relationships` SET term_taxonomy_id = %s WHERE term_taxonomy_id IN (" . sqlPlaceholder($merge_from,'%d') . ")",$p);
$r2 = $wpdb->query($update2);
foreach ($merge_from as $i) {
    wp_delete_term( $i, 'character' );
}