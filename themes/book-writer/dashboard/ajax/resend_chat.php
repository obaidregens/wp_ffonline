<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax']) && isset($_POST['to'])){
    global $this_user;
    $this_user = get_user_by('id',$_POST['to'])->data;
    get_template_part('dashboard/part','chatdiv');
 exit;
}