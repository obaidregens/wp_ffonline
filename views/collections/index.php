<?php
if (! isset($app->bundle)){
    $app->bundle = global_bundle('collection-index');
}
$app->bundle->css('css/components/grid');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/components/select');
$app->bundle->css('css/views/collection-content');
$app->bundle->css('css/views/collection-options');
$app->bundle->js('js/views/collection-options');
$app->bundle->js('js/views/collection-content');
$app->bundle->js('js/views/search-updateCollection');
$app->bundle->css('css/views/search-updateCollection');
$app->bundle->enqueue();

$args = array(
    'order'         => $_GET['sort'] ?? 'DESC',
    'orderby'       => 'created',
    'types'         => array('Public'),
    'count'         => [
        'from'  => 1
    ]
);
$type = _landing::get_type();
if ($type['type'] === 'author-collections'){
    $args['author_included'] = array($type['type_id']);
    $args['types'] = array('Public','Favorites');
    unset($args['count']);
    if (is_current_user($type['type_id'])){
        $args['types'] = array('Public','Favorites','Private','Unlisted');
    }
}
$collections = collection::query($args);
?>
<collection-bar>
<button label="Create New"></button>
<button label="My Collections"></button>
</collection-bar>
<script>
    window.collections_data = <?= script_json(json_encode(collection_helpers::js_data())); ?>;
</script>
<collections-container class="grid"><?php
foreach ($collections as $collection) {
    $app->collection = $collection;
    $app->template('subviews/collection');
}
if (empty($collections)) {
    $app->template('subviews/no-collections');
}
?></collections-container>
