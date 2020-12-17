<?php
$app->bundle = global_bundle('connections-home');
$app->bundle->css('css/components/notices');;
$app->bundle->css('css/views/connections-home');
$app->bundle->js('js/views/connections-home');
if (1===2) {
    ?><span>Importing stories from Fanfiction.Net is currently disabled. Read more <a href="/@admin">here</a>.</span><?php
} else {
    $connected = c_user::current();
    $pending = c_user::pending();
    if (is_string($pending)){ $pending = htmlspecialchars($pending); }
    if (is_string($connected)){ $connected = htmlspecialchars($connected); }
    $status_class = $connected ? "linked" : ($pending ? "pending" : "");
    ?>
    <?php if ($connected !== false) { ?>
    <p>Your FFN account has been linked. <a href="/import-stories">Import Stories</a>.</p>
    <?php } ?>
    <connections>
        <a source="ffn">
            <img src="<?= STATIC_URL() ?>images/ffnlogo.png">
            <text>Fanfiction.Net</text>
            <button class="<?= $status_class ?>"></button>
        </a>
    </connections>
    <enter-input source="ffn">
        <?php if ($status_class === 'pending') { ?>
            <p>Verification for the FFN account "<?= $pending; ?>" is pending. <a class="cancel-verification">Cancel</a></p>
            <p>If you've already sent the code, please wait. It may take up to 30 min to process.</p>
        <?php } else if ($status_class === 'linked') { ?>
            <p>The FFN account "<?= $connected; ?>" has been linked. You can now <a href="/import-stories">import your stories</a>.</p>
        <?php } if ($status_class === "") { ?>
            <important>Please take care to enter your User ID and <strong>NOT</strong> your username.</important>
            <p>Enter your FFN User ID: </p>
            <text-input label="FFN User ID" maxlength="10"></text-input>
            <button label="Submit"></button>
            <completed></completed>
            <verification-code></verification-code>
            <h3>Where do I get my FFN User ID?</h3>
            <ol>
                <li>To find your FFN User ID, go to your <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/account/settings.php">account settings.</a>.</li>
                <li>Under Account Settings, your FFN User ID will be second on the list labeled "Userid".</li>
            </ol>
        <?php } ?>
    </enter-input>
    <?php
}