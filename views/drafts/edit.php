<?php
$app->bundle = global_bundle('drafts-edit');
$app->bundle->mix('react');
$app->bundle->mix('slate');
$app->bundle->js('external/timeago/timeago');
$app->bundle->css('css/components/dropdown');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/js-components/next-screen');
$app->bundle->js('js/components/next-screen');
$app->bundle->css('css/js-components/confirmation');
$app->bundle->js('js/components/confirmation');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/drafts-editReact');
$app->bundle->js('js/views/drafts-edit');
$app->bundle->css('css/views/drafts-edit');
$app->bundle->css('css/views/drafts-editThesaurus');
$app->bundle->js('js/views/drafts-editThesaurus');
$app->bundle->css('css/views/drafts-editRevisions');
$app->bundle->js('js/views/drafts-editRevisions');
$app->bundle->css('css/views/drafts-editButtons');
$app->bundle->js('js/views/drafts-editButtons');
$app->bundle->js('js/views/drafts-editPost');
$app->bundle->enqueue();
$draft = $app->draft;
$content = $draft === false ? '' : $draft->content;
$title = $draft === false ? 'Untitled' : $draft->title;
$draft_id = $draft === false ? 'new' : $draft->ID;
$share_link = $draft === false ? '' : ($draft->share === null ? '' : home_url( '/drafts/' . $draft->share));
?>
<?php if (current_user_can('administrator')) { ?>
    <button style="display:block;margin-left: auto;margin-top: 20px;" label="Post"></button>
<?php } ?>
<?php if (! is_user_logged_in()) { ?>
    <important>Draft is not being saved. <a onclick="prompt_login();">Login</a> to save your drafts.</important>
<?php } ?>
<save-time datetime="<?= $draft === false ? 0 : intval($draft->edited)*1000; ?>" ></save-time>
<drafts-header>
<a href="/drafts">Back to Drafts</a>
<button label="Preview"></button>
<button class="dropdown" label="Export">
    <dropdown class="right">
        <a>FFN</a>
        <a>AO3</a>
    </dropdown>
</button>
</drafts-header>
<share-is hidden><?=  $share_link ?></share-is>
<load_title hidden><?= $title; ?></load_title>
<load_content hidden><?= $content; ?></load_content>

<input maxlength="70" placeholder="Title">
<editor draft_id="<?= $draft_id; ?>"></editor>