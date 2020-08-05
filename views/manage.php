<?php
$app->bundle = global_bundle('manage');
$app->bundle->css('css/components/collapsible');
$app->bundle->css('css/components/index');
$app->bundle->enqueue();
echo reports::average_time_by_referrer();
echo reports::popular_link_ins();