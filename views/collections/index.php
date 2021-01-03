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
$app->bundle->js('js/views/global-collections');
$app->bundle->css('css/views/global-collections');
$args = array(
    'order'         => $_GET['sort'] ?? 'DESC',
    'orderby'       => 'created',
    'types'         => array('Public'),
    'count'         => [
        'from'  => 1
    ]
);
$landing = landing::now();
if ($landing->type === 'author-collections'){
    $args['author_included'] = [$landing->type_id];
    $args['types'] = array('Public','Favorites');
    unset($args['count']);
    if (is_current_user($landing->type_id)){
        $args['types'] = array('Public','Favorites','Private','Unlisted');
    }
}
$collections = collection::query($args);
?>
<collection-bar>
<button label="Create New"></button>
<?php if ($type['type'] !== 'author-collections') { ?>
<button label="My Collections"></button>
<?php } ?>
</collection-bar>
<collections-container class="grid"><?php
foreach ($collections as $collection) {
    $app->collection = $collection;
    $app->template('/subviews/collection');
}
if (empty($collections)) {
    $app->template('/subviews/no-collections');
}
?></collections-container>
