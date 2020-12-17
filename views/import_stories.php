<?php
$app->bundle = global_bundle('import-stories');
$app->bundle->mix('confirmation');
$app->bundle->css('css/js-components/checkbox');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/views/import-main');
$app->bundle->js('js/views/import-main');
$allow_reimport = in_array(intval(get_current_user_id()),get_option( 'reimport_allow', [] ));
$connected = c_user::current();
$pending = c_user::pending();
if (is_string($pending)){ $pending = htmlspecialchars($pending); }
if (is_string($connected)){ $connected = htmlspecialchars($connected); }

if (1===2) {
    ?><span>Importing stories from Fanfiction.Net is currently disabled. Read more <a href="/@admin">here</a>.</span><?php
}
else if ($pending !== false) {
    ?>
    <important>
        Verification is pending. After linking, you'll be able to import all your stories with a single tap.
        <br>
        Forgot verification code? <a href="/connections">Request new</a>.
    </important>
    <?php
}
else if ($connected === false) {
    ?><important><a href="/connections">Link</a> your FFN account to import your stories.</important><?php
}
else {
$remote_stories = import_stories::view_all($connected);
?>
<h2>Stories to Import</h2>
<label class="checkbox">
    <input type="checkbox" check-all>
    <text>Select All</text>
</label>
<?php foreach($remote_stories as $story) { ?>
<?php if ( in_array($story['status'],['imported','live','reimport']) ) { ?>
<label class="imported">
    <text><?= htmlspecialchars($story['title']); ?></text>
    <?php if ($story['status'] === "live") { ?>
        <a story_id="<?= $story['storyID']; ?>" action="disable_autoupdate"></a>
        <a href="/story/<?= $story['storyID']; ?>">Read</a>
    <?php } else if ($story['status'] === "reimport") { ?>
        <status>(Re-import pending)</status>
        <a href="/story/<?= $story['storyID']; ?>">Read</a>
        <a story_id="<?= $story['storyID']; ?>" action="cancel_reimport"></a>
    <?php } else { ?>
        <a href="/story/<?= $story['storyID']; ?>">Read</a>
        <?php if ($allow_reimport) { ?>
        <a story_id="<?= $story['storyID']; ?>" action="reimport"></a>
        <?php } ?>
    <?php } ?>
</label>
<?php } else { ?>
<label class="checkbox">
    <input <?= $story['status'] === 'pending' ? 'checked' : ''; ?> type="checkbox" value="<?= $story['ID'] ?>">
    <text><?= htmlspecialchars($story['title']) ?></text>
    <status><?= $story['status'] === 'pending' ? '(Pending)' : ''; ?></status>
</label>
<?php } ?>
<?php } ?>
<button label="Import"></button>
<?php
}
?>