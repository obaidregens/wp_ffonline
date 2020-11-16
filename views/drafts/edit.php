<?php
$bundle = new bundle('drafts-editSlate-ps');
$bundle->mix('react');
$bundle->mix('slate');
$bundle->js('external/slate/isHotkey');
$app->bundle = global_bundle('drafts-edit');
$app->bundle->js('external/timeago/timeago');
$app->bundle->css('css/components/notices');
$app->bundle->css('css/components/index');
$app->bundle->css('css/components/collapsible');
$app->bundle->css('css/components/loader');
$app->bundle->mix('confirmation');
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
$app->bundle->js('js/views/drafts-editPublish');
$app->bundle->css('css/views/drafts-editPublish');
$app->bundle->css('css/views/story-content');
$app->bundle->js('js/views/drafts-editShortcuts');
$app->bundle->css('css/views/drafts-editShortcuts');
$draft = $app->draft;
$draft_id = $draft === false ? 'new' : $draft->ID;
?>
<?php if (current_user_can('administrator')) { ?>
    <button style="display:block;margin-left: auto;margin-top: 20px;" label="Post"></button>
<?php } ?>
<?php if (! is_user_logged_in()) { ?>
    <important>Draft is not being saved. <a onclick="prompt_login();">Login</a> to save your drafts.</important>
<?php } ?>
<save-time></save-time>
<drafts-header>
    <a href="/drafts">Back to Drafts</a>
    <button label="Publish"></button>
    <button class="dropdown" label="Export">
        <dropdown class="right">
            <a tabindex="0">FFN</a>
            <a tabindex="0">AO3</a>
        </dropdown>
    </button>
</drafts-header>
<input maxlength="80" placeholder="Title">
<editor draft_id="<?= $draft_id; ?>"></editor>

<?php
// $bundleDev = new bundle("drafts-editSlateDev");
// $bundleDev->mix('react');
// $bundleDev->mix('react_dev');

// $bundleDev1 = new bundle("drafts-editSlateDev1");
// $bundleDev1->script_type = 'text/jsx';
// $bundleDev1->js('js/views/drafts-editReact.jsx');

// $bundleDev->print();
// $bundleDev1->print();


$bundle->print();