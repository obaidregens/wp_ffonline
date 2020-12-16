<?php
// Delete Account

// Online Users
// SELECT
//     wp_users.user_login,
//     stats_landings.type,
//     stats_landings.type_id,
//     (UNIX_TIMESTAMP() - stats_actions.timestamp) as passed
// FROM `stats_actions`
// INNER JOIN stats_landings ON stats_landings.ID = stats_actions.landing_id
// INNER JOIN wp_users ON stats_landings.user_id = wp_users.ID
// WHERE wp_users.user_login != 'admin'
// GROUP BY stats_landings.ID
// ORDER BY stats_actions.ID DESC
// LIMIT 50

// Recent Referrers
// SELECT
// 	CONCAT('fanfiction.online',request) as request,
//     CONCAT(referrer_host,referrer_path) as referrer,
//     COUNT(*) as count FROM `stats_landings`
// WHERE referrer_host IS NOT NULL
// AND referrer_host != 'local'
// AND timestamp > (UNIX_TIMESTAMP() - 12*60*60)
// GROUP BY referrer
// ORDER BY count DESC
$app->bundle = global_bundle('dash-home');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
$app->bundle->css('css/views/dash-home');
global $wpdb;

$landings = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings");

$t = time() - (60*60*24*7);
$landings_last_week = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings WHERE timestamp > $t AND user_id != 12");
$t = time() - (60*60*24);
$landings_last_24 = $wpdb->get_results("SELECT COUNT(*) as c FROM stats_landings WHERE timestamp > $t AND user_id != 12");

$users_last_24 = $wpdb->get_results("SELECT COUNT(*) as c FROM wp_users WHERE user_registered > DATE_SUB(NOW(), INTERVAL 24 HOUR)");

$t = time() - (60*60*24*7);
$unique_landings_last_week = $wpdb->get_results("SELECT COUNT(DISTINCT vfs) as c FROM stats_landings WHERE timestamp > $t AND user_id != 12");
$t = time() - (60*60*24);
$unique_landings_last_24 = $wpdb->get_results("SELECT COUNT(DISTINCT vfs) as c FROM stats_landings WHERE timestamp > $t AND user_id != 12");
///////////////////////////////////ONLINE
$t = time() - (60*1.5);
$online = $wpdb->get_results(
    "SELECT COUNT(DISTINCT(stats_landings.user_id)) as online_users,COUNT(DISTINCT(stats_landings.vfs)) as online_vfs
    FROM stats_actions
    INNER JOIN stats_landings ON stats_actions.landing_id = stats_landings.ID
    WHERE stats_actions.timestamp > $t"
);
?>
<overview>
<block label="Online User" count="<?= $online[0]->online_users; ?>"></block>
<block label="Online Visitors" count="<?= $online[0]->online_vfs; ?>"></block>
</overview>
<overview>
<block label="Users" count="<?= $wpdb->get_results("SELECT COUNT(*) as c FROM wp_users WHERE user_status = 0")[0]->c; ?>"></block>
<block label="Published Stories" count="<?= (new book_query(['per_page'=>1]))->count; ?>"></block>
<block label="Hits" count="<?= $landings[0]->c ?>"></block>
</overview>
<overview>
<block label="Hits - Last 7 days" count="<?= $landings_last_week[0]->c ?>"></block>
<block label="Hits - Last 24 hours" count="<?= $landings_last_24[0]->c ?>"></block>
</overview>
<overview>
<block label="Users - Last 24 hours" count="<?= $users_last_24[0]->c ?>"></block>
</overview>
<overview>
<block label="Unique Visitors - Last 7 days" count="<?= $unique_landings_last_week[0]->c ?>"></block>
<block label="Unique Visitors - Last 24 hours" count="<?= $unique_landings_last_24[0]->c ?>"></block>
</overview>