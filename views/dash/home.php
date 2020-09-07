<?php
$app->bundle = global_bundle('dash-home');
$app->bundle->css('css/views/dash-home');
$app->bundle->js('js/views/dash-home');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->enqueue();
global $wpdb;
$landings = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings");
$t = time() - 60*60*24*7;
$landings_last_week = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings WHERE timestamp > $t");
$t = time() - 60*60*24;
$landings_last_24 = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings WHERE timestamp > $t");
$users_last_24 = $wpdb->get_results("SELECT COUNT(*) as c FROM wp_users WHERE user_registered > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
?>
<overview>
<block label="Users" count="<?= count(get_users()); ?>"></block>
<block label="Published Stories" count="<?= (new book_query([]))->count; ?>"></block>
<block label="Hits" count="<?= $landings[0]->c ?>"></block>
</overview>
<overview>
<block label="Hits - Last 7 days" count="<?= $landings_last_week[0]->c ?>"></block>
<block label="Hits - Last 24 hours" count="<?= $landings_last_24[0]->c ?>"></block>
</overview>
<overview>
<block label="Users - Last 24 hours" count="<?= $users_last_24[0]->c ?>"></block>
</overview>