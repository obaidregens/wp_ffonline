<?php
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
$base_load = $maindir . "/content/php_includes/mail/PhpMimeMailParser/";
$files = [
    "Contracts/CharsetManager.php",
    "Contracts/Middleware.php",
    "Charset.php",
    "MimePart.php",
    "Attachment.php",
    "Exception.php",
    "Middleware.php",
    "MiddlewareStack.php",
    "Parser.php"
];
foreach ($files as $f) {
    require_once $base_load . $f;
}
$parser = new PhpMimeMailParser\Parser();
$parser->setStream(fopen("php://stdin", "r"));

// empty vars
$from = $parser->getAddresses('from')[0]['address'];
$to = $parser->getAddresses('to')[0]['address'];
$subject = $subject = $parser->getHeader('subject');
$headers = json_encode($parser->getHeaders());
$plaintext = $parser->getMessageBody('text');
$html = $parser->getMessageBody('html');
$splittingheaders = true;
// Insert
define('WP_USE_THEMES', false);
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
        'message'       => $html
    ]
);