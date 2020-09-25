<?php
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

function email(array $args) {
    $e = new err();
    $args = array_replace([
        'from'      => 'noreply',
        'subject'   => "Message from Fanfiction Online",
        'reply-to'  => "",
        'template'  => "",
        'params'    => [],
        'txtparams' => [],
        'htmlparams'=> [],
    ],$args);

    $args['to'] = (array) ($args['to'] ?? []);
    if ( empty($args['to']) ){
        return $e->add('to',"Which email to send to?");
    }
    ob_start();
    $html_template = file_get_contents(__DIR__ . '/templates/' . $args['template'] . '.html');
    $txt_template = file_get_contents(__DIR__ . '/templates/' . $args['template'] . '.txt');
    ob_end_clean();
    if ( $html_template === false || $txt_template === false ){
        return $e->add('template',"Invalid template");
    }
    // Permanent Settings
    $mail = new PHPMailer(true);
    $mail->CharSet="UTF-8";
    $mail->isSMTP();
    $mail->Host = defined("SMTP_HOST") ? SMTP_HOST : "";
    $mail->SMTPAuth = defined("SMTP_AUTH") ? SMTP_AUTH : true;
    $mail->Port = defined("SMTP_PORT") ? SMTP_PORT : 0;
    $mail->SMTPSecure = defined("SMTP_SECURE") ? SMTP_SECURE : "tls";

    global $email_creds;
    // Variable Settings
    $cred = &$email_creds[$args['from']];
    if (! isset($cred)) {
        return $e->add('from','No credentials found');
    }
    $mail->Username = $cred['email'];
    $mail->Password = $cred['pass'];
    $mail->setFrom($cred['email'], $cred['name']);
    $mail->addReplyTo($cred['email'], $cred['name']);

    // Replace Params in Template
    foreach ($args['htmlparams'] as $k => $v) {
        $html_template = str_replace($k,$v,$html_template);
    }
    foreach ($args['txtparams'] as $k => $v) {
        $txt_template = str_replace($k,$v,$txt_template);
    }
    foreach ($args['params'] as $k => $v) {
        $html_template = str_replace($k,$v,$html_template);
        $txt_template = str_replace($k,$v,$txt_template);
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = $args['subject'];
    $mail->Body = $html_template;
    $mail->AltBody = $txt_template;
    if (trim($args['reply-to']) !== "") {
        $mail->addCustomHeader('References', $args['reply-to']);
        $mail->addCustomHeader('In-Reply-To', $args['reply-to']);    
    }
    
    foreach ($args['to'] as $to) {
        $mail->addAddress( $to );
    }
    try {
        $mail->send();
        return $mail->getLastMessageID();
    } catch (Exception $error) {
        return $e->add('sending',"Message not sent");
    }
}