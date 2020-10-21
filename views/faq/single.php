<?php
$app->bundle = global_bundle('faq');
$app->bundle->css('css/views/faq-main');
$app->bundle->js('js/views/faq-main');
$app->bundle->enqueue();
?>
<a class="back-to-faq" href="/faq">Back to FAQ</a>
<question><?= htmlspecialchars($app->faq_question->question); ?></question>
<answer><?= strip_tags($app->faq_question->answer,'<a><strong>'); ?></answer>
<?php
?>