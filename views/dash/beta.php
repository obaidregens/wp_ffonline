<?php
$app->bundle = global_bundle('dash-beta');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
$app->bundle->css('css/components/overview');
$app->bundle->css('css/components/index');
$app->bundle->js('js/views/dash-beta');
$current = beta::get_current();
?>
<?php if (!$current) { ?>
<text-input label="Description" maxlength="5000" type="multi"></text-input>
<text-input label="Duration" input_type="number">12</text-input>
<text-input label="Users" input_type="number">15</text-input>
<text-input label="Start URL">read</text-input>
<text-input helper="Separate by commas" label="Custom Users"></text-input>
<button label="Create" style="float:right;"></button>
<?php } else { ?>
<?php
global $wpdb;
$sql = $wpdb->prepare(
    "SELECT
        stats_landings.user_id as u,
        COUNT(stats_landings.user_id) as c,
        wp_users.user_login as username,
        stats_landings.timestamp as first_visit
    FROM stats_landings
    INNER JOIN wp_users ON wp_users.ID = stats_landings.user_id
    WHERE user_id IN (" . sqlPlaceholder($current['users']) . ")
    AND timestamp > %d
    AND host = 'beta'
    GROUP BY user_id",
    array_merge($current['users'],[intval($current['start_time'])/1000])
);
$users_visited = $wpdb->get_results($sql);
?>
<h2>Beta Ongoing</h2>
<p style="white-space:pre;"><?= $current['description']; ?></p>
<overview>
<block count="<?= count($current['users']); ?>" label="Users Invited"></block>
<block count="<?= count($users_visited); ?>" label="Users Visited"></block>
</overview>
<overview>
<block count="<?= human_time_diff( time(), round(intval($current['end_time'])/1000) ) ?>" label="Ending In"></block>
</overview>
<h3>Users</h3>
<index>
<li head>
    <cell>Username</cell>
    <cell>First Visit</cell>
</li>
<?php foreach ($users_visited as $row) { ?>
    <li>
        <cell>@<?= $row->username; ?></cell>
        <cell><?= human_time_diff($row->first_visit); ?> ago</cell>
    </li>
<?php } ?>
</index>
<?php } ?>