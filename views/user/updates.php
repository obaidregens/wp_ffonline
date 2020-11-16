<?php
$app->template('/views/user/header');
$app->bundle->css('/css/views/updates-add');
$app->bundle->css('/css/views/updates-content');
$app->bundle->js('/js/views/updates-add');
$app->bundle->js('/js/views/updates-content');

$app->updates_count = 100;
$app->template('subviews/updates');