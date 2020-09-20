<?php
$app->bundle = global_bundle('dash-contact');
$app->bundle->css('css/components/floater');
$app->bundle->css('css/js-components/sidenav');
$app->bundle->js('js/components/sidenav');
$app->bundle->js('js/views/dash-main');
// Unique
$app->bundle->css('css/views/dash-contact');
$app->bundle->js('js/views/dash-contact');
$app->bundle->enqueue();
global $wpdb;
$r = $wpdb->get_results("SELECT * FROM contact ORDER BY received_time ASC");
$re = [];
foreach ($r as $message ) {
    $conv = $message->from;
    if ($conv === "Contact" || (explode('@',$conv)[1] ?? '') === "fanfiction.online"){
        $conv = $message->to;
    }
    $k = &$re[htmlspecialchars($conv)];
    $k = $k ?? [];
    $k[] = [
        'message'   => htmlspecialchars( ($message->subject === "" ? "" : "<h3>" . $message->subject . "</h3><br>") . $message->message),
        'time'      => intval(floatval($message->received_time) * 1000),
        'from'      => htmlspecialchars($message->from),
        'user_id'   => $message->user_id
    ];
}
?>
<json-data data="<?= htmlspecialchars(json_encode($re)); ?>" hidden></json-data>
<with class="show">
<?php foreach ($re as $user => $conv ) { ?>
    <single <?= is_current_user($conv[count($conv)-1]['user_id']) ? "hidden" : "" ?> ><?= $user; ?></single>
<?php } ?>
</with>
<toggle><?= "Open Users" ?></toggle>

<message-box>
<messages></messages>
<text-input label="Reply" type="multi"></text-input>
<button label="Reply"></button>
</message-box>