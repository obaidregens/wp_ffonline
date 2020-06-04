<style>
    .sort-collections{
        margin-bottom: 60px;
    }
    .sort-order-wrapper i {
        font-size: 22px;
    }
</style>
<?php
get_header();
if (! isset($_GET['sort']) || ! in_array(strtoupper($_GET['sort']),array('ASC','DESC'))){
    $_GET['sort'] = 'DESC';
}
if (! isset($_GET['sortby']) || ! in_array($_GET['sortby'],array('created','count'))){
    $_GET['sortby'] = 'created';
}
global $js_bundle;
$js_bundle = global_bundle('collection-index');
$js_bundle->add('collection-index');
$js_bundle->enqueue();
?>
<?php
if (is_author()){
	get_template_part( 'author/header' );	
}
?>
<main class="mobile-margin">
<div class="row">
    <select value="<?= $_GET['sortby'] ?>" class="col s10 m11 sort-collections browser-default">
        <option value="count">Books</option>
        <option value="created">Time Created</option>
    </select>
    <div class="col s2 m1 sort-order-wrapper">
        <button class="btn-hover"><i class="fas fa-sort-amount-down<?= $_GET['sort'] == 'ASC' ? '-alt' : '';?>"></i></button>
    </div>
</div>
<?php
$args = array(
    'order'         => $_GET['sort'],
    'orderby'       => $_GET['sortby'],
    'types'         => array('Public'),
    'count'         => array(
        'from'  => 1
    )
);
if (is_author()){
    $args['types'] = array('Favorites');
    $args['authors'] = array($wp_query->queried_object->ID);
    if ($wp_query->queried_object->ID === get_current_user_id()){
        $args['types'][] = 'Private';
        $args['types'][] = 'Unlisted';
    }
}
$collections = collection::query($args);
global $collection;
foreach ($collections as $collection) {
    get_template_part('collections/content');
}
?>
</main>

<?php
get_footer();
exit();