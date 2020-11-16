<?php
$app->bundle = global_bundle('dash-beta');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
$app->bundle->css('css/components/index');
$app->bundle->js('js/views/dash-reimport');
$users = get_option( 'reimport_allow', [] );
?>
<index>
    <li head>
        <cell>User ID</cell>
        <cell>Delete</cell>
    </li>
    <?php foreach($users as $id) { ?>
    <?php $user = get_user_by( "ID", $id ); ?>
    <li user_id="<?= $user->ID; ?>">
        <cell>@<?= $user->user_login; ?></cell>
        <cell><button label="Delete"></button></cell>
    </li>
    <?php } ?>
</index>
<text-input label="Username" prefix="@"></text-input>
<button style="float: right;" label="Submit"></button>