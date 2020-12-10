<?php
$app->bundle = global_bundle('help-home');
$app->bundle->css('css/js-components/autocomplete');
$app->bundle->js('js/components/autocomplete');
$app->bundle->css('css/views/help-home');
$app->bundle->js('js/views/help-home');
?>
<text-input label="Search for Help"></text-input>
<sections>
    <a href="/help/guides">Guides</a>
    <a href="/help/faq">FAQ</a>
</sections>
<p>Nothing found? <a href="/help/faq">Ask us</a>.</p>