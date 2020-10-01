<?php
$app->bundle = global_bundle('faq');
$app->bundle->css('css/views/faq-main');
$app->bundle->js('js/views/faq-main');
$app->bundle->enqueue();
?>
<question><?= $app->faq_question->question; ?></question>
<answer><?= $app->faq_question->answer; ?></answer>
<?php
?>