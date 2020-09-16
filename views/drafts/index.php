<?php
$app->bundle = global_bundle('drafts-index');
$app->bundle->js('external/draggable/draggable.bundle');
$app->bundle->css('css/js-components/ask');
$app->bundle->js('js/components/ask');
$app->bundle->css('css/js-components/confirmation');
$app->bundle->js('js/components/confirmation');
$app->bundle->css('css/components/folders');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/views/drafts-index');
$app->bundle->css('css/views/drafts-indexNew');
$app->bundle->css('css/views/drafts-indexActions');
$app->bundle->js('js/views/drafts-index');
$app->bundle->js('js/views/drafts-indexNew');
$app->bundle->js('js/views/drafts-indexActions');
$app->bundle->enqueue();
?>
<floater>
    <button class="new popup"></button>
</floater>
<popup new-action>
    <a label="Draft" href="/drafts/new"></a>
    <a label="Folder"></a>
</popup>
<folder-listing></folder-listing>