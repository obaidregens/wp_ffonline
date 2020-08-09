<?php
$app->template('/views/user/header');
$app->bundle->css('/css/components/collapsible');
$app->bundle->js('/js/components/switch');
$app->bundle->css('/css/js-components/switch');
$app->bundle->css('/css/views/author-settings');
$app->bundle->js('/js/views/author-settings');
$app->bundle->enqueue();
$ffn_user = c_user::current();
?>
<h2 label="Account"></h2>
<input class="collapsible" name="settings" type="radio">
<label>Change Password</label>
<collapsible>
    <text-input input_type="password" label="Password"></text-input>
    <text-input input_type="password" label="Confirm Password"></text-input>
    <button label="Change"></button>
</collapsible>