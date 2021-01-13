<?php
$app->bundle = global_bundle('feedback');
$app->bundle->css("css/components/loader");
$app->bundle->css("css/views/feedback-index");
$app->bundle->css("css/views/feedback-topic");
?>

<?php
// $bundle = new bundle('react-ps');
// $bundle->mix('react');
// $bundle->print();

$bundleDev = new bundle("feedback-dev");
$bundleDev->mix('react');
$bundleDev->mix('react_dev');

$bundleDev1 = new bundle("feedback-dev2");
$bundleDev1->script_type = 'text/jsx';
$bundleDev1->js('js/views/feedback-react.jsx');

$bundleDev->print();
$bundleDev1->print();