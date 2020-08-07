<?php
$app->bundle = global_bundle('drafts-index');
$app->bundle->css('css/components/grid');
$app->bundle->css('css/views/drafts-index');
$app->bundle->enqueue();
$drafts = drafts::by_users();
?>
<drafts class="grid">
    <a href="/drafts/edit/new" class="draft new-draft grid-item"></a>
    <?php foreach ($drafts as $draft) { ?>
        <?php $content = json_decode($draft->content)[0]->children[0]->text; ?>
        <?php
        $substr = substr($content,0,200);
        if (strlen($content) > 200){
            $substr .= '...';
        }
        ?>
        <a href="/drafts/edit/<?= $draft->ID; ?>" class="draft grid-item">
            <updated><?= human_time_diff( intval($draft->updated) ); ?></updated>
            <draft-title><?= $draft->title; ?></draft-title>
            <excerpt><?= $substr; ?></excerpt>
        </a>
    <?php } ?>
</drafts>
