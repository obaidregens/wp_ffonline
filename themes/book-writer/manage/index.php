<?php
/* Template Name: Dashboard */ 
admin_only();
global $bundle;
$bundle = global_bundle('manage');
$bundle->js('js/manage');
$bundle->css('css/components/index');
$bundle->enqueue();
get_header();
echo reports::average_time_by_referrer();
get_footer( );
