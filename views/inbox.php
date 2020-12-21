<?php
$app->bundle = global_bundle('inbox');
$app->bundle->mix('confirmation');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/views/inbox-list');
$app->bundle->js('js/views/inbox-list');

$bundle = new bundle('react-ps');
$bundle->mix('react');
$bundle->print();

// $bundleDev = new bundle("inbox-dev");
// $bundleDev->mix('react');
// $bundleDev->mix('react_dev');

// $bundleDev1 = new bundle("inbox-dev2");
// $bundleDev1->script_type = 'text/jsx';
// $bundleDev1->js('js/views/inbox-list.jsx');

// $bundleDev->print();
// $bundleDev1->print();