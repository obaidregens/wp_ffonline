<?php
$app->template('/views/user/header');
$app->bundle->mix('updates-content');
$app->bundle->css('/css/views/updates-add');
$app->bundle->js('/js/views/updates-add');

$app->updates_count = 100;
$app->template('/subviews/updates');