<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['page'])){
    //if (current_user_can('administrator')){
        //get_template_part('dashboard-test/part',$_POST['page']);
    //}
    //else{
        get_template_part('dashboard/part',$_POST['page']);
    //}
    
 exit;
}