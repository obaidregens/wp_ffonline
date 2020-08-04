<?php
$app->bundle = global_bundle('verify');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/components/select');
$app->bundle->css('css/views/verify-main');
$app->bundle->js('js/views/verify-main');
$app->bundle->enqueue();

$logged_in = is_user_logged_in(  );
$connected = c_user::current();
?>
<?php if ($connected !== false) { ?>
<h5>Your account is connected to a <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/u/<?= $connected; ?>">FFN account</a>.</h5>
<?php } else { ?>
<h1>Verify access to account.</h1>
<ol>
<?php if (! $logged_in) { ?>
<li>Create an account on Fanfiction Online. Or if you already have one, login.</li>
<li>Visit this <a href="/verify">page</a>.</li>
<?php } ?>
<li>Enter your FFN User ID & Submit.</li>
<li>You'll be provided with a verification code. Send this verification code as a private message to the account linked.</li>
<li>Your FFN account will be connected to your Fanfiction Online account.</li>
</ol>
<important>
If your account doesn't get connected within half an hour of sending the message, you should <a href="/contact">contact</a> us.
</important>
<?php if ($logged_in) { ?>
<verification-account></verification-account>
<verification-code></verification-code>
<select>
<option>FFN</option>
</select>
<text-input maxlength="10" label="User ID"></text-input>
<button label="Submit"></button>
<?php } ?>
<?php } ?>