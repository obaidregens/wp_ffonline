<?php
/* Template Name: Dashboard */ 
admin_only();
global $bundle;
$bundle = global_bundle('manage');
$bundle->js('js/manage');
$bundle->css('css/components/collapsible');
$bundle->css('css/components/index');
$bundle->enqueue();
get_header();
echo reports::average_time_by_referrer();
echo reports::popular_link_ins();
get_footer( );
