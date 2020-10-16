<?php
$app->bundle = global_bundle('verify');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/components/select');
$app->bundle->css('css/views/verify-main');
$app->bundle->js('js/views/verify-main');
$app->bundle->enqueue();

$logged_in = is_user_logged_in(  );
$connected = c_user::current();
$pending = c_user::pending();
if (is_string($pending)){ $pending = htmlspecialchars($pending); }
if (is_string($connected)){ $connected = htmlspecialchars($connected); }
?>
<?php if ($pending !== false && $connected === false) { ?>
<important>Verification is pending for access to <a rel="nofollow" href="https://www.fanfiction.net/u/<?= $pending; ?>">this</a> FFN account.<br>If you've already sent the code, don't worry! It sometimes takes up to an hour to process the verification.</important>
<?php } ?>
<?php if ($connected !== false) { ?>
<exciting>You've been verified as the author of <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/u/<?= $connected; ?>">this</a> FFN account. You can now <a href="/import-stories">import</a> your stories from there.</exciting>
<?php } else { ?>
<h1>Verify yourself</h1>
<p>Verify yourself as the author to easily <a href="/import-stories">import</a> your stories from other sites.</p>
<ol>
<?php if (! $logged_in) { ?>
<li>Create an account on Fanfiction Online. Or if you already have one, <a onclick="prompt_login()">login</a>.</li>
<li>Visit this <a href="/verify">page</a>.</li>
<?php } ?>
<li>
    Enter your FFN User ID below & Submit.
    <ol>
        <li>To find your User ID, go to your <a rel="nofollow" href="https://www.fanfiction.net/account/settings.php">account settings.</a></li>
        <li>Under <strong>Account Settings</strong>, your User ID will be second on the list.</li>
    </ol>
</li>
<li>You'll be provided with a verification code. Send this verification code as a private message to the account linked.</li>
<li>Your FFN account will be connected to your Fanfiction Online account.</li>
</ol>
<important>
If your account doesn't get connected within an hour of sending the message, you should <a href="/contact">contact</a> us.
</important>
<?php if ($logged_in) { ?>
<verification-account></verification-account>
<verification-code></verification-code>
<select>
<option>FFN</option>
<option>AO3</option>
</select>
<text-input maxlength="10" label="User ID"><?= $app->params['author_id'] ?? ''; ?></text-input>
<button label="Submit"></button>
<?php } ?>
<?php } ?>