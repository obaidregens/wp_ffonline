<?php
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
require_once($maindir . '/wp-config.php');
if (!defined("DISABLE_WP_CRON")) {
    define('DISABLE_WP_CRON', 'true');
}