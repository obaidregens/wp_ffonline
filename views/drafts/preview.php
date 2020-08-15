<?php
$app->bundle = global_bundle('drafts-preview');
$app->bundle->css('css/components/grid');
$app->bundle->css('css/components/dropdown');
$app->bundle->css('css/js-components/next-screen');
$app->bundle->js('js/components/next-screen');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/drafts-preview');
$app->bundle->js('js/views/drafts-preview');
$app->bundle->css('css/views/drafts-index');
$app->bundle->enqueue();
$draft = $app->draft;
$user = get_userdata( $draft->user_id );
$draft_read = (drafts_json::read($draft->content));
$all_drafts = drafts::by_users();
$app->link_draft = false;
?>
<share-link hidden><?= $draft->share; ?></share-link>
<button draft_id="<?= $draft->ID; ?>" class="edit-draft"></button>
<button class="popup" label="Compare"></button>
<popup select-draft>
    <h3>Compare Draft</h3>
    <drafts class="grid">
        <?php foreach ($all_drafts as $draft) {
            $app->draft = $draft;
            $app->template('/subviews/draft');
        } ?>
    </drafts>
</popup>
<!-- Draft -->
<?php if ($app->type === 'drafts-share') { ?>
    <draft-meta>Shared by <a href="<?= get_author_posts_url( $user->ID ); ?>">@<?= $user->user_login ?></a></draft-meta>
<?php } ?>
<draft-title><?= $draft->title; ?></draft-title>
<draft class="acs-elem"><?= $draft_read; ?></draft>