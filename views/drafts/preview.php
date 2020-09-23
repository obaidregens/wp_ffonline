<?php
$app->bundle = global_bundle('drafts-preview');
$app->bundle->js('external/hammer-js/hammer');
$app->bundle->css('css/views/chapter-acs');
$app->bundle->js('js/views/chapter-acs');
$app->bundle->css('css/views/drafts-index');
$app->bundle->js('js/views/drafts-index');
$app->bundle->css('css/views/drafts-preview');
$app->bundle->js('js/views/drafts-preview');
$app->bundle->css('css/components/folders');
$app->bundle->enqueue();
$draft = $app->draft;
$user = get_userdata( $draft->user_id );
$draft_read = drafts_json::read($draft->content);
?>
<share-link hidden><?= $draft->share; ?></share-link>
<button draft_id="<?= $draft->ID; ?>" class="edit-draft"></button>
<button label="Compare" class="popup"></button>
<popup>
<folder-listing></folder-listing>
</popup>
<!-- Draft -->
<?php if ($app->type === 'drafts-share') { ?>
    <draft-meta>Shared by <a href="<?= get_author_posts_url( $user->ID ); ?>">@<?= $user->user_login ?></a></draft-meta>
<?php } ?>
<draft-title><?= $draft->title; ?></draft-title>
<draft class="acs-elem"><?= $draft_read; ?></draft>