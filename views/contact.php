<?php
$app->bundle = global_bundle('contact');
$app->bundle->js('js/views/contact-main');
$app->bundle->enqueue();
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
<p>If you need any help, or have any suggestions for us, just shoot us a message through the form below.</p>
<text-input input_type="email" label="Email"></text-input>
<text-input label="Send Message" type="multi"></text-input>
<recaptcha></recaptcha>
<button label="Send"></button>