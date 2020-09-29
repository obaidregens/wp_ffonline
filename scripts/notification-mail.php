<?php
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');
require_once $maindir . '/content/php_includes/mail/mail.php';

// Settings
$not_older_than_hours = 24;

// Setup
$greater_than = time() - ($not_older_than_hours*60*60);
global $wpdb;
$sql = $wpdb->prepare(
"SELECT * FROM notifications WHERE email_status = 'none' AND timestamp > %d"
,[$greater_than]);
$r = $wpdb->get_results($sql);
if (empty($r)) {
    echo 'No notifications';
    exit();
}
$users = $wpdb->get_results(
"SELECT * FROM wp_users
WHERE ID IN(" . implode(',',array_column($r,'user_id')) . ")"
,OBJECT_K);
$lastOpened = array_column($wpdb->get_results(
    "SELECT stats_landings.user_id,stats_actions.timestamp FROM stats_actions
    INNER JOIN stats_landings ON stats_actions.landing_id = stats_landings.ID
    AND stats_actions.stat = 'notifications'
    ORDER BY stats_actions.timestamp ASC"
),'timestamp','user_id');
$by_users = [];
foreach ($r as $v) {
    if ( intval($lastOpened[$v->user_id]) > intval($v->timestamp)  ) {
        continue;
    }
    $ref = &$by_users[$v->user_id];
    $ref = $ref ?? [];
    $ref[] = $v;
}
if (empty($by_users)) {
    echo 'No notifications';
    exit();
}
$unread_count = array_column($wpdb->get_results(
    "SELECT chats.to as b,COUNT(chats.to) as c FROM chats
    WHERE `to` IN(" . implode(',',array_keys($by_users)) . ")
    AND `status` = 'sent'
    GROUP BY `to`
    ORDER BY milli_timestamp DESC"
),'c','b');
$mail = new email;
foreach ($by_users as $notifications) {
    $prioritized = [];
    foreach ($notifications as $not) {
        $notification = notifications::createNotification($not);
        if ($notification === null) {continue;}
        $notification['ID'] = $not->ID;
        $prioritized[notifications::getPriority($not->notification_type)] = $notification;
    }
    if (empty($prioritized)) {
        continue;
    }
    ksort($prioritized);
    $prioritized = array_values($prioritized);

    $notification = $prioritized[0];

    $total = count($prioritized);
    $mail->subject = $notification['message'];
    $user = $users[$not->user_id] ?? null;
    if ($user === null) {continue;}
    // More string
    $more_notifications = $total - 1;
    $more_messages = intval($unread_count[$user->ID] ?? 0);
    $more_str = '';
    if (max($more_messages,$more_notifications) > 0) {
        $more_str .= "You have ";
        $more_str .= $more_notifications > 0 ? "$more_notifications notifications " : "";
        $more_str .= min($more_messages,$more_notifications) > 0 ? 'and ' : '';
        $more_str .= $more_messages > 0 ? "$more_messages new messages " : "";
        $more_str .= 'waiting for you.';
    }
    $mail->txtparams = [
        '###USERNAME###'            => "@" . $user->user_login,
        '###NOTIFICATION###'        => $notification['message'],
        '###NOTIFICATION_LINK###'   => $notification['link'],
        '###MORE_NOTIFICATIONS###'  => $more_str,
    ];
    $mail->setTemplate('notification');
    $mail->send($user->user_email);
    $query = $wpdb->prepare("
    UPDATE notifications
    SET email_status = 'sent'
    WHERE ID IN (" . implode(',',array_fill(0,count($prioritized),'%d')) . ") 
    ",array_column($prioritized,'ID'));
    $wpdb->query($query);
}
$mail->close();
echo "Completed <br>\n";
echo "Sent: {$mail->sent}<br>\n";
echo "Unsent: {$mail->unsent}<br>\n";