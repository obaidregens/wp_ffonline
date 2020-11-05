<?php
$app->template('/views/user/header');
$app->bundle->css('/css/components/collapsible');
$app->bundle->js('/js/components/switch');
$app->bundle->css('/css/js-components/switch');
$app->bundle->css('/css/views/author-settings');
$app->bundle->js('/js/views/author-settings');
$app->bundle->enqueue();
$ffn_user = c_user::current();
$change_username_meta = get_user_meta( get_current_user_id(), 'last_change_username', true );
$change_username = $change_username_meta === "" ? 1 : (intval($change_username_meta) > time() - 60*60*24*30 ? 0 : 1);
?>
<h2 label="Account"></h2>
<input class="collapsible" name="settings" type="radio">
<label>Change Password</label>
<collapsible>
    <text-input input_type="password" label="Password"></text-input>
    <text-input input_type="password" label="Confirm Password"></text-input>
    <button label="Change"></button>
</collapsible>
<input class="collapsible" name="settings" type="radio">
<label>Change Username</label>
<collapsible>
    <span>Changing your username will also change the location of your profile. Direct visits to your old profile link will stop working.</span>
    <text-input prefix="@" label="New Username"></text-input>
    <span>You have <strong><?= $change_username; ?></strong> available username change.</span>
    <button <?= $change_username > 0 ? "" : "disabled"; ?> label="Change"></button>
</collapsible>
<switch <?= user_settings::get('features') ? "checked" : "" ?> setting="features" label="Notify me of surveys & polls for upcoming features."></switch>
<switch <?= follow::exists('user',user::get_by('login','admin')->ID) ? 'checked' : ''; ?> setting="news" label="Notify of news & new features."></switch>