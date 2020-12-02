<?php
$app->bundle = global_bundle('faq');
$app->bundle->css('css/components/collapsible');
$app->bundle->css('css/views/faq-main');
$app->bundle->js('js/views/faq-main');
$questions = questions::by_category();
$current_user = user::get_by("ID",get_current_user_id());
?>
<faq-header>Frequently Asked Questions</faq-header>
<?php
foreach ($questions as $cat => $qs ) {
    ?><question-category label="<?= $cat; ?>"><?php
    foreach ($qs as $question) {
        ?>
        <question-wrapper class="waves-effect">
        <question><?= htmlspecialchars($question->question); ?></question>
        <answer><answer-content><?= strip_tags($question->answer,'<a><strong>'); ?></answer-content></answer>
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
<p>If you don't think your question should be posted in the FAQ's, you can message <a href="/inbox/@mods">@mods</a> or send us a message through the <a href="/contact">contact form</a>.</p>
<h2>Ask a question</h2>
<ul>
<li>You may ask one question per submission. If you have multiple questions, ask them separately.</li>
<li>Your question may be rephrased before being posted in the FAQ's.</li>
</ul>
<text-input helper="This is optional. You can add your email to receive a notification when your question is answered." input_type="email" label="Your Email"><?= $current_user ? $current_user->user_email : ""; ?></text-input>
<text-input type="multi" label="Your Question"></text-input>
<recaptcha></recaptcha>
<button label="Ask"></button>
<notice></notice>