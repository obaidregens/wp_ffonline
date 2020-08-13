<?php
$app->bundle = global_bundle('drafts-index');
$app->bundle->css('css/components/grid');
$app->bundle->css('css/views/drafts-index');
$app->bundle->enqueue();
$drafts = drafts::by_users();
?>
<drafts class="grid">
    <a href="/drafts/new/edit" class="draft new-draft grid-item"></a>
    <?php foreach ($drafts as $draft) {
        $app->draft = $draft;
        $app->template('/subviews/draft');
    } ?>
</drafts>