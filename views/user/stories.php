<?php
$app->template('/views/user/header');
$user = $app->user;
$is_current_author = is_current_user($user->ID);
$app->template('/views/search');