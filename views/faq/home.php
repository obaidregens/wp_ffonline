<?php
$app->bundle = global_bundle('faq');
$app->bundle->css('css/components/collapsible');
$app->bundle->css('css/views/faq-main');
$app->bundle->js('js/views/faq-main');
$app->bundle->enqueue();
$questions = questions::by_category();
?>
<faq-header>Frequently Asked Questions</faq-header>
<?php
foreach ($questions as $cat => $qs ) {
    ?><question-category label="<?= $cat; ?>"><?php
    foreach ($qs as $question) {
        ?>
        <question-wrapper class="waves-effect">
        <question><?= htmlspecialchars($question->question); ?></question>
        <answer><?= strip_tags($question->answer,'<a><strong>'); ?></answer>
        </question-wrapper>
        <?php
    }
    ?></question-category><?php
}
if (empty($questions)) {
    ?><text>No questions yet. If you have a question, you can ask below.</text><?php
}
// Ask New Question
?>
<h2>Ask a question</h2>
<ul>
<li>Question will be asked anonymously.</li>
<li>You may ask one question per submission. If you have multiple questions, ask them separately.</li>
<li>Your question may be rephrased before being posted in the FAQ's.</li>
<li>If you don't want your question to be posted in the FAQ's, you can <a href="/contact">ask us directly</a>.</li>
</ul>
<text-input input_type="email" label="Your Email"></text-input>
<helper-text>This is optional. You can add your email to receive a notification when your question is answered.</helper-text>
<text-input type="multi" label="Your Question"></text-input>
<recaptcha></recaptcha>
<button label="Ask"></button>
<notice></notice>