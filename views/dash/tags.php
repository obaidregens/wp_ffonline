<?php
$app->bundle = global_bundle('dash-tags');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
$app->bundle->css('css/components/select');
$app->bundle->css('css/components/index');
$app->bundle->css('css/views/dash-tags');
$app->bundle->js('js/views/dash-tags');
$current = $_GET['tax'] ?? 'rating';
$terms = get_terms( array(
    'taxonomy'      => $current,
    'hide_empty'    => false,
    'parent'        => 0
) );
$tax_all = ['category','rating','language','status','genre','character'];
?>
<select>
<?php foreach ($tax_all as $tax ) { ?>
    <option <?= $tax === $current ? 'selected' : '' ?>><?= ucfirst($tax); ?></option>
<?php } ?>
</select>
<index>
<li head>
    <cell>Name</cell>
    <cell>Count</cell>
</li>
<?php foreach ($terms as $term ) { ?>
<li>
    <cell><?= $term->name; ?></cell>
    <cell><?= $term->count; ?></cell>
</li>
<?php } ?>
</index>
<text-input label="Name"></text-input>
<button label="Create"></button>