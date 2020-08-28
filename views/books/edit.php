<?php
$book = $app->book;
$book_id = $book === 'new' ? 'new' : $book->ID;
?>
<tags_data hidden><?= json_encode(get_data($book_id)); ?></tags_data>
<exciting></exciting>
<stepper>
    <step>Book Details</step>
    <step>Book Tags</step>
    <step>Book Settings</step>
    <step>Chapters</step>
</stepper>
<book></book>
<page page-num="4"></page>
<submit>
<button theme label="Save"></button>
</submit>
<?php
$app->bundle = global_bundle('edit-book');
$app->bundle->css('css/components/notices');
$app->bundle->mix('react');
$app->bundle->js('external/draggable/draggable.bundle');
$app->bundle->css('css/js-components/switch');
$app->bundle->css('css/components/index');
$app->bundle->css('css/js-components/ask');
$app->bundle->js('js/components/ask');
$app->bundle->css('css/components/folders');
$app->bundle->css('css/views/drafts-index');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/views/book-edit');
$app->bundle->js('js/views/book-edit-submit');
$app->bundle->css('css/views/book-edit-submit');
$app->bundle->js('js/views/drafts-index');
$app->bundle->enqueue();
$app->bundle->print();
$app->bundle = new bundle('edit-book-react');
$app->bundle->script_type = 'module';
$app->bundle->js('js/views/book-edit');
$app->bundle->enqueue();
?>
