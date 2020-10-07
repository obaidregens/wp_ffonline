<?php
exit();
$merge_from = [37174,37180,37197,37219,37231,37236,37243,37275,37306];
$merge_to_id = 37343;

ignore_user_abort(true);

define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
require( $maindir . "/content/wp/wp-load.php");

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
wp_update_term_count_now( [$merge_to_id], 'character' );