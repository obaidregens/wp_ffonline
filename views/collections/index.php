<?php
if (! isset($app->bundle)){
    $app->bundle = global_bundle('collection-index');
}
$app->bundle->css('css/components/grid');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/views/collection-content');
$app->bundle->js('js/views/collection-options');
$app->bundle->js('js/views/collection-content');
$app->bundle->enqueue();

$args = array(
    'order'         => $_GET['sort'] ?? 'ASC',
    'orderby'       => 'count',
    'types'         => array('Public'),
    'count'         => [
        'from'  => 1
    ]
);
$type = _landing::get_type();
if ($type['type'] === 'author-collections'){
    $args['authors'] = array($type['type_id']);
    $args['types'] = array('Public','Favorites');
    if (intval(get_current_user_id()) === intval($type['type_id'])){
        $args['types'] = array('Public','Favorites','Private','Unlisted');
    }
}
$collections = collection::query($args);
?><collections-container class="grid"><?php
foreach ($collections as $collection) {
    $app->collection = $collection;
    $app->template('subviews/collection');
}
?></collections-container>