<?php
$app->bundle = global_bundle('create-fandom');
$app->bundle->mix('autocomplete');
$app->bundle->css('css/components/select');
$app->bundle->js('js/views/createFandom-main');
$app->bundle->css('css/views/createFandom-main');
$app->bundle->enqueue();
$terms = get_terms([
    'taxonomy'  => 'category',
    'parent'    => 0,
    'hide_empty'=> false
]);
$fandoms = get_terms([
    'taxonomy'      => 'category',
    'exclude'       => array_column($terms,'term_id'),
    'hide_empty'    => false,
    'fields'        => 'names'
]);
?>
<fandoms hidden><?= json_encode($fandoms); ?></fandoms>
<h2>Create Fandom</h2>
<select>
<?php foreach ( $terms as $term ){ ?>
<option value="<?= $term->term_id; ?>"><?= $term->name; ?></option>
<?php } ?>
</select>
<label label="Category"></label>
<text-input maxlength="100" label="Fandom Name"></text-input>
<button disabled label="Create"></button>