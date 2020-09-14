<?php
$app->bundle = global_bundle('import-stories');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/views/import-main');
$app->bundle->js('js/views/import-main');
$app->bundle->enqueue();
$connected = c_user::current();
$pending = c_user::pending();

if ($pending !== false) {
    ?><important>Verification is pending for access to <a rel="nofollow" href="https://www.fanfiction.net/u/<?= $pending; ?>">this</a> FFN account. After verification, you'll be able to import all your stories with a single tap.<br>Forgot your verification code? <a href="/verify">Get new</a>.</important><?php
}
else if ($connected === false) {
    ?><important>Your account hasn't been connected to an FFN account yet. Please <a href="/verify">complete the verification</a> to import your stories.</important><?php
}
else {
$remote_stories = import_stories::view_all($connected);
?>
<h2>Stories to Import</h2>
<?php foreach($remote_stories as $story) { ?>
<?php if ($story['status'] === 'imported') { ?>
<label class="imported">
    <text><?= $story['title']; ?></text>
    <a href="/story/<?= $story['storyID']; ?>">Read</a>
</label>
<?php } else { ?>
<label class="checkbox">
    <input <?= $story['status'] === 'pending' ? 'checked' : ''; ?> type="checkbox" value="<?= $story['ID'] ?>">
    <text><?= $story['title'] ?></text>
    <status><?= $story['status'] === 'pending' ? '(Pending)' : ''; ?></status>
</label>
<?php } ?>
<?php } ?>
<button label="Import"></button>
<?php
}
?>