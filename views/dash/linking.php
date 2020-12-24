<?php
$app->bundle = global_bundle('dash-linking');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
// Unique
$app->bundle->mix('confirmation');
$app->bundle->css('css/components/index');
$app->bundle->js('js/views/dash-linking');
global $wpdb;
$r = $wpdb->get_results(
    "SELECT
        user_connections.`ID` as `ID`,
        user_connections.`connection_user` as `ffn_user`,
        wp_users.`display_name` as `name`
    FROM user_connections
    INNER JOIN wp_users ON wp_users.ID = user_connections.user_id
    WHERE status = 'unverified'"
);
?>
<index>
<?php
foreach ($r as $row) {
    if (!is_numeric($row->ffn_user)) {
        continue;
    }
    ?>
    <single>
        <cell><?= $row->name; ?></cell>
        <cell>
            <a rel="nofollow" href="https://fanfiction.net/u/<?= $row->ffn_user ?>"><?= $row->ffn_user; ?></a>
        </cell>
        <cell>
            <a cid="<?=$row->ID; ?>">Confirm</a>
        </cell>
    </single>
    <?php
}
?>
</index>