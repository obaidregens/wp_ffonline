<?php
$app->bundle = global_bundle('guides-home');
$app->bundle->css('css/views/guides-home');
$app->bundle->js('js/views/guides-home');
?>
<h2>Guides</h2>
<p>Guides to help you get started quickly.</p>
<?php
$index = json_decode(file_get_contents(MAIN_DIR . "/content/tours/index.json"),true);
foreach ($index as $guide ) {
    ?>
    <guide name="<?= $guide['name']; ?>">
        <h4><?= $guide['Title']; ?></h4>
        <p><?= $guide['Description']; ?></p>
    </guide>
    <?php
}
?>