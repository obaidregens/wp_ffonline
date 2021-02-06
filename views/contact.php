<?php
$app->bundle = global_bundle('contact');
$app->bundle->js('js/views/contact-main');

$user = user::get(get_current_user_id());
?>
<style>
button[label="Send"] {
    float: right;
}
p {
    font-size: 0.95rem;
}
main {
    max-width: 600px !important;
}
</style>
<h3>Contact us</h3>
<p>If you have any questions, send us a message through the form below. We'll get back to you as quickly as possible.</p>
<?php if ($user) { ?>
<p>Logged in as @<?= $user->user_login; ?></p>
<?php } ?>
<text-input input_type="email" label="Email"><?= $user->user_email ?? "" ?></text-input>
<text-input label="Send Message" type="multi"></text-input>
<recaptcha></recaptcha>
<button label="Send"></button>