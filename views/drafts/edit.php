<?php
$app->bundle = global_bundle('drafts-edit');
$app->bundle->mix('react');
$app->bundle->mix('slate');
$app->bundle->css('css/components/dropdown');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/js-components/confirmation');
$app->bundle->js('js/components/confirmation');
$app->bundle->js('js/views/drafts-editReact');
$app->bundle->js('js/views/drafts-edit');
$app->bundle->css('css/views/drafts-edit');
$app->bundle->enqueue();
$draft = $app->draft;
$content = $draft === false ? '' : $draft->content;
$title = $draft === false ? '' : $draft->title;
$draft_id = $draft === false ? 'new' : $draft->ID;
$share_link = $draft === false ? '' : ($draft->share === null ? '' : home_url( '/drafts/' . $draft->share));
?>
<?php if (current_user_can('administrator')) { ?>
    <button style="display:block;margin-left: auto;margin-top: 20px;" label="Post"></button>
<?php } ?>
<?php if (! is_user_logged_in()) { ?>
    <important>Autosaving is disabled. <a onclick="prompt_login();">Login</a> to enable.</important>
<?php } ?>
<autosave-time></autosave-time>
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