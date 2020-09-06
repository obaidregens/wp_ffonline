<?php
$app->bundle = global_bundle('dash-home');
$app->bundle->css('css/views/dash-home');
$app->bundle->js('js/views/dash-home');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->enqueue();
global $wpdb;
$landings = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings");
?>
<overview>
<block label="Users" count="<?= count(get_users()); ?>"></block>
<block label="Published Stories" count="<?= (new book_query([]))->count; ?>"></block>
<block label="Hits" count="<?= $landings[0]->c ?>"></block>
</overview>