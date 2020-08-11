<?php
$book = $app->book;
?>
<tags_data hidden><?= json_encode(get_data($book->ID)); ?></tags_data>
<book></book>
<submit>
<button theme label="Save"></button>
</submit>
<?php
$app->bundle = global_bundle('edit-book');
$app->bundle->mix('react');
$app->bundle->css('css/js-components/switch');
$app->bundle->css('css/components/select');
$app->bundle->css('css/js-components/ask');
$app->bundle->js('js/components/ask');
$app->bundle->css('css/views/book-edit');
$app->bundle->js('js/views/book-edit-submit');
$app->bundle->css('css/views/book-edit-submit');
$app->bundle->enqueue();
$app->bundle->print();
$app->bundle = new bundle('edit-book-react');
$app->bundle->script_type = 'module';
$app->bundle->js('js/views/book-edit');
$app->bundle->enqueue();
?>