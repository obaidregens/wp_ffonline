<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['page_chain'])){
    //Parse Page Chain
    define('page_chain_arr', array_filter(explode('/',$_POST['page_chain']), function ($elem) {
        if ($elem != ""){
            return $elem;
        }
    }));
    $pages = array('write','profile','stats','messages','chat','settings','bookmarks','collections');
    if (! in_array(page_chain_arr[0],$pages)){
        get_template_part('dashboard/part','dashboard');
    }
    else{
        get_template_part('dashboard/part',page_chain_arr[0]);
    }
    
 exit;
}