<?php
$app->template('/views/user/header');
$user = $app->user;
$is_current_author = intval(get_current_user_id()) === intval($user->ID);

$app->template('/views/collections/index');