<?php
$app->bundle = global_bundle('drafts-index');
$app->bundle->css('css/js-components/ask');
$app->bundle->js('js/components/ask');
$app->bundle->css('css/components/grid');
$app->bundle->css('css/components/folders');
$app->bundle->css('css/views/drafts-index');
$app->bundle->css('css/views/drafts-indexNew');
$app->bundle->js('js/views/drafts-index');
$app->bundle->js('js/views/drafts-indexNew');
$app->bundle->enqueue();
?>
<button class="new popup"></button>
<popup new-action>
    <a label="Draft" href="/drafts/new"></a>
    <a label="Folder"></a>
</popup>
<folder-listing></folder-listing>