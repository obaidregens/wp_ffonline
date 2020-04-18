<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['fandom']) && isset($_POST['category'])){
    $return = wp_insert_term($_POST['fandom'],'category',array('parent'=>$_POST['category']));
    echo is_wp_error($return);
 exit;
}