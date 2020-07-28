<?php
if (! isset($app->bundle)){
    $app->bundle = global_bundle('collection-index');
}
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/views/collection-content');
$app->bundle->js('js/views/collection-options');
$app->bundle->js('js/views/collection-content');
$app->bundle->css('css/views/collection-single');
$app->bundle->enqueue();

?><collections-container><?php
$app->template('/subviews/collection');
?></collections-container><?php
$app->template('/views/search');