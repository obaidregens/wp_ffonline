<?php
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
define("NO_ROUTES",true);
require ($maindir . '/content/index.php');

require_once $maindir . '/content/php_includes/mail/mail.php';

// Settings
$not_older_than_hours = 5;

// Setup
$greater_than = time() - ($not_older_than_hours*60*60);
global $wpdb;
$sql = $wpdb->prepare(
"SELECT * FROM notifications WHERE email_status = 'none' AND timestamp > %d"
,[$greater_than]);
$r = $wpdb->get_results($sql);
$unread_messages = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT chats.to as b,COUNT(chats.to) as c,chats.milli_timestamp as millitime FROM chats
        WHERE `status` = 'sent'
        AND milli_timestamp > %d
        GROUP BY `to`
        ORDER BY milli_timestamp DESC",
        [millitime()-1.75*60*60*1000]
    ),
    OBJECT_K
);
if (empty($r) && empty($unread_messages)) {
    echo 'No notifications';
    exit();
}
$user_ids_with_notifications = array_merge(array_column($r,'user_id'),array_keys($unread_messages));
$users = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM wp_users
        WHERE ID IN(" . sqlPlaceholder($user_ids_with_notifications) . ")",
        $user_ids_with_notifications
    )
,OBJECT_K);
$lastOpened = array_column($wpdb->get_results(
    $wpdb->prepare(
        "SELECT stats_landings.`user_id`,stats_actions.`timestamp` FROM stats_actions
        INNER JOIN stats_landings ON stats_actions.`landing_id` = stats_landings.`ID`
        AND stats_actions.stat = 'notifications'
        AND stats_landings.`user_id` IN (" . sqlPlaceholder($user_ids_with_notifications) . ")
        ORDER BY stats_actions.timestamp ASC",
        $user_ids_with_notifications    
    )
),'timestamp','user_id');

$by_users = [];
foreach ($r as $v) {
    if ( intval($lastOpened[$v->user_id] ?? 0) > intval($v->timestamp)  ) {
        continue;
    }
    $ref = &$by_users[$v->user_id];
    $ref = $ref ?? [];
    $ref[] = $v;
}
foreach ($unread_messages as $user_id => $v) {
    if ( intval($lastOpened[$user_id] ?? 0) > intval($v->millitime)/1000  ) {
        continue;
    }
    $ref = &$by_users[$user_id];
    $ref = $ref ?? [];
}
if (empty($by_users)) {
    echo 'No notifications';
    exit();
}
$mail = new email;
foreach ($by_users as $user_id => $notifications) {
    $user = $users[$user_id] ?? null;
    if ($user === null) {continue;}
    $more_messages = intval($unread_messages[$user->ID]->c ?? 0);

    $prioritized = [];
    foreach ($notifications as $k => $not) {
        $notification = notifications::createNotification($not);
        if ($notification === null) {continue;}
        $notification['ID'] = $not->ID;
        $pr = notifications::getPriority($not->notification_type);
        $prioritized[$pr+$k] = $notification;
    }
    if (empty($prioritized) && $more_messages <= 0) {
        continue;
    }
    $msg_s = $more_messages > 1 ? "s" : "";
    if (empty($prioritized)) {
        $mail->subject = "You have $more_messages new message$msg_s!";
        $mail->txtparams = [
            '###USERNAME###'            => "@" . $user->user_login,
            '###NOTIFICATION###'        => $mail->subject,
            '###NOTIFICATION_LINK###'   => "https://fanfiction.online/inbox",
            '###MORE_NOTIFICATIONS###'  => "",
        ];
    }
    else {
        ksort($prioritized);
        $prioritized = array_values($prioritized);
    
        $notification = $prioritized[0];
    
        $total = count($prioritized);
        $mail->subject = $notification['message'];
        $user = $users[$not->user_id] ?? null;
        if ($user === null) {continue;}
        // More string
        $more_notifications = $total - 1;
        $more_str = '';
        if (max($more_messages,$more_notifications) > 0) {
            $more_str .= "You have ";
            $more_str .= $more_notifications > 0 ? "$more_notifications notifications " : "";
            $more_str .= min($more_messages,$more_notifications) > 0 ? 'and ' : '';
            $more_str .= $more_messages > 0 ? "$more_messages new message$msg_s " : "";
            $more_str .= 'waiting for you.';
        }
        $mail->txtparams = [
            '###USERNAME###'            => "@" . $user->user_login,
            '###NOTIFICATION###'        => $notification['message'],
            '###NOTIFICATION_LINK###'   => $notification['link'],
            '###MORE_NOTIFICATIONS###'  => $more_str,
        ];    
    }
    $mail->setTemplate('notification');
    $mail->send($user->user_email);
    if (!empty($prioritized)) {
        $query = $wpdb->prepare("
        UPDATE notifications
        SET email_status = 'sent'
        WHERE ID IN (" . implode(',',array_fill(0,count($prioritized),'%d')) . ") 
        ",array_column($prioritized,'ID'));
        $wpdb->query($query);
    }
}
$mail->close();
echo "Completed <br>\n";
echo "Sent: {$mail->sent}<br>\n";
echo "Unsent: {$mail->unsent}<br>\n";