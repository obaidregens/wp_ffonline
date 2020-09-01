<?php
$t = time();
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');

$dir = $maindir . '/content/scripts/category-ffn/';
$cat_files = array_diff(scandir($dir), array('..', '.'));
function term_replace($term, $taxonomy, $parent){
    $exists = term_exists($term,$taxonomy,$parent);
    if ($exists !== null){
        return $exists['term_id'];
    }
    $term_id = wp_insert_term($term,$taxonomy,array(
            'parent'      => $parent,
        )
    );
    return $term_id;
}
foreach ( $cat_files as $filename ) {
    $cat = ucfirst(substr($filename,0,strlen($filename) - 5));
    $cat_id = term_replace( $cat, 'category', 0 );
    $fandoms = file($dir . $filename);
    foreach ( $fandoms as $k => $fandom_name ) {
        $err = wp_insert_term( $fandom_name, 'category', [
            'parent'    => $cat_id
        ]);
        unset($fandoms[$k]);
        file_put_contents($dir . $filename,implode("",$fandoms));
        if (time() - $t > 110) {
            echo 'Timeout';
            exit();
        }
    }
}
echo 'Completed';
exit();