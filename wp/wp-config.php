<?php
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
require_once($maindir . '/wp-config.php');
define('DISABLE_WP_CRON', 'true');