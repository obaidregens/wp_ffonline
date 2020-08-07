<?php
$app->bundle = global_bundle('drafts-preview');
$app->bundle->css('css/components/dropdown');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/drafts-preview');
$app->bundle->js('js/views/drafts-preview');
$app->bundle->enqueue();
$draft = $app->draft;
$draft_read = (drafts_json::read($draft->content));
?>
<share-link hidden><?= $draft->share; ?></share-link>
<button draft_id="<?= $draft->ID; ?>" class="edit-draft"></button>
<draft-title><?= $draft->title; ?></draft-title>
<draft class="acs-elem"><?= $draft_read; ?></draft>