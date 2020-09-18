<?php
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');

global $wpdb;

$sql =
"
SELECT wp_term_taxonomy.term_id,wp_term_taxonomy.count,wp_terms.name,wp_term_relationships.object_id
FROM wp_term_taxonomy
INNER JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id
INNER JOIN wp_term_relationships ON wp_term_taxonomy.term_id = wp_term_relationships.term_taxonomy_id
WHERE wp_term_taxonomy.taxonomy = 'pairing'
";
$results = $wpdb->get_results($sql);
$terms = empty($results) ? [] : get_terms([
    'taxonomy'  => 'character',
    'object_ids'=> array_column($results,'object_id'),
    'fields'    => 'all_with_object_id',
]);
$characters = [];
foreach ($terms as $term) {
    $k = &$characters[$term->object_id];
    $k = $k ?? [];
    $k[] = ['term_id'=>$term->term_id,'name'=>$term->name];
}
foreach ( $results as $row ) {
    $c = $characters[$row->object_id];
    $chars = [];
    foreach ($c as $cc ) {
        if ( strpos($row->name, $cc['name'] ) !== false) {
            $chars[] = $cc['term_id'];
        }
    }
    $pairingId = pairing::add($row->object_id,$chars);
    wp_delete_term( $row->term_id, 'pairing' );
}
