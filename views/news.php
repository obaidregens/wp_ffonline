<style>
    author-updates::before {
        content: '' !important;
    }
    p {
        margin-block-start: 0;
    }
</style>
<h2>News</h2>
<?php
$app->bundle = global_bundle('news');
$app->bundle->css('/css/components/dropdown');
$app->bundle->css('/css/views/updates-content');
$app->bundle->js('/js/views/updates-content');
$app->bundle->enqueue();
$app->updates_count = -1;
$app->user = get_user_by( 'login', 'ffonline' );
ob_start();
$app->template('/subviews/updates');
$p = ob_get_contents();
ob_end_clean();
echo $p;
if (trim($p) === '') {
    echo 'No news yet.';
}