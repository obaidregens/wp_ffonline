<?php
$app->bundle = global_bundle('inbox');
$app->bundle->css('css/components/loader');
$app->bundle->css('css/components/tooltips');
$app->bundle->css('css/views/inbox-list');
$app->bundle->js('js/views/inbox-list');
$app->bundle->enqueue();

$chats_with = chats::with();
?>
<chat-list>
    <?php foreach ($chats_with as $key => $chat) { ?>
        <chat username="@<?= $chat->username; ?>" unread="<?= $chat->unread; ?>"></chat>
    <?php } ?>
</chat-list>
<user-info>
    <button class="back"></button>
    <a href="/@ffonline" target="_blank">@ffonline</a>
    <button tooltip-bottom="Block" class="block"></button>
</user-info>
<messages></messages>
<send-message>
<text-input label="Message"></text-input>
<button disabled></button>
</send-message>
<loader xl></loader>