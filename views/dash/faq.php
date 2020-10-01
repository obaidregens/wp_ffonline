<?php
$app->bundle = global_bundle('dash-faq');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
// Unique
$app->bundle->css('css/views/dash-faq');
$app->bundle->js('js/views/dash-faq');
$app->bundle->enqueue();
$questions = questions::query([
    'status' => ['pending']
]);
?>
<questions>
<?php foreach ($questions as $q) { ?>
    <question-wrapper question-id="<?= $q->ID ?>">
        <question><?= $q->question; ?></question>
        <a>Reply</a>
    </question-wrapper>
<?php } ?>
</questions>
<reply>
    <blockquote></blockquote>
    <a>Edit</a>
    <text-input type="multi" label="Reply"></text-input>
    <button label="Reply"></button>
</reply>