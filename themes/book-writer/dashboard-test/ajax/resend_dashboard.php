<?php
define('WP_USE_THEMES', false);
require('/home3/eshaatco/public_html/fic/wp-load.php');
if( isset($_POST['ajax'])){
    get_template_part('dashboard/part','dashboarddiv');
 exit;
}