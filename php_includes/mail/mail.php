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
        'plaintext' =>  "",
        'html'      =>  "",
    ],$args);
    if ( empty($args['to']) ){
        return $e->add('to',"Which email to send to?");
    }
    if ( trim($args['plaintext']) === "" && trim($args['html']) === "" ){
        return $e->add('message',"What's the email?");
    }
    $args['to'] = (array) $args['to'];
    // Permanent Settings
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = defined("SMTP_HOST") ? SMTP_HOST : "";
    $mail->SMTPAuth = defined("SMTP_AUTH") ? SMTP_AUTH : true;
    $mail->Port = defined("SMTP_PORT") ? SMTP_PORT : 0;
    $mail->SMTPSecure = defined("SMTP_SECURE") ? SMTP_SECURE : "tls";

    global $email_creds;
    // Variable Settings
    $cred = &$email_creds[$args['from']];
    $mail->Username = $cred['email'];
    $mail->Password = $cred['pass'];
    $mail->setFrom($cred['email'], $cred['name']);
    $mail->addReplyTo($cred['email'], $cred['name']);

    // Content
    $mail->isHTML(false);
    $mail->Subject = $args['subject'];
    $mail->Body = $args['plaintext'];
    // if (trim($args['html'])  !== "") {
    //     $mail->AltBody = $args['plaintext'];
    // }

    foreach ($args['to'] as $to) {
        $mail->addAddress( $to );
    }
    try {
        $mail->send();
        return true;
    } catch (Exception $error) {
        return $e->add('sending',"Message not sent");
    }
}