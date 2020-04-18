<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
if( isset($_POST['ajax'])){
    get_template_part('dashboard/part','dashboarddiv');
 exit;
}