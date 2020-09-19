#!/usr/bin/php -q
<?php
// Script from
// https://stackoverflow.com/questions/3468937/how-to-insert-incoming-e-mail-message-into-mysql-database/3469203
chdir(dirname(__FILE__));
$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);

if(strlen($email)<1) {
    die(); 
}

// handle email
$lines = explode("\n", $email);

// empty vars
$from = "";
$to="";
$subject = "";
$headers = "";
$message = "";
$splittingheaders = true;

for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";
        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
        if (preg_match("/^To: (.*)/", $lines[$i], $matches)) {
            $to = $matches[1];
        }
    } else {
        // not a header, but message
        $message .= $lines[$i]."\n";
    }
    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}

// Insert
define('WP_USE_THEMES', false);
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$wp_dir = $maindir . '/content/wp/';
require( $wp_dir . 'wp-load.php');
global $wpdb;
$wpdb->insert(
    'contact',
    [
        'user_id'       => 0,
        'from'          => $from,
        'to'            => $to,
        'received_time' => microtime(true),
        'subject'       => $subject,
        'headers'       => $headers,
        'message'       => $message
    ]
);