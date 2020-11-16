<?php
$book = $app->story;
$book_id = $book === 'new' ? 'new' : $book->ID;
?>
<exciting></exciting>
<?php
?>
<stepper>
    <step>Story Details</step>
    <step>Story Tags</step>
    <step>Story Settings</step>
    <step>Chapters</step>
</stepper>
<book book_id="<?= $book_id; ?>"></book>
<page page-num="4"></page>
<submit>
<?php
if ($book_id !== 'new' && $book->post_status === 'publish') {
    ?><a theme class="button" href="<?= get_permalink( $book_id ); ?>" label="View"></a><?php
}
?>
<button theme label="Save"></button>
</submit>
<?php
$bundle = new bundle('react-select-ps');
$bundle->js('external/react-select/react-select');
$bundle->print();

$app->bundle = global_bundle('edit-book');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/components/select');
$app->bundle->css('css/components/collapsible');
$app->bundle->js('external/draggable/draggable.bundle');
$app->bundle->css('css/js-components/switch');
$app->bundle->css('css/components/index');
$app->bundle->css('css/js-components/ask');
$app->bundle->js('js/components/ask');
$app->bundle->css('css/components/folders');
$app->bundle->css('css/components/tooltips');
$app->bundle->js('js/views/drafts-index');
$app->bundle->css('css/views/drafts-index');
$app->bundle->css('css/views/book-edit');
$app->bundle->js('js/views/book-edit-submit');
$app->bundle->css('css/views/book-edit-submit');
$app->bundle->js('js/views/book-edit');
