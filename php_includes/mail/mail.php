<?php
require __DIR__ . '/PHPMailer/PHPMailer.php'; 
require __DIR__ . '/PHPMailer/SMTP.php'; 
require __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.mailtrap.io';
$mail->SMTPAuth = true;
$mail->Username = '1a2b3c4d5e6f7g';
$mail->Password = '1a2b3c4d5e6f7g';
$mail->SMTPSecure = 'tls';
$mail->Port = 2525;

// Content
$mail->Subject = 'Test Email via Mailtrap SMTP using PHPMailer';
$mail->isHTML(false);
$mail->setFrom('info@mailtrap.io', 'Mailtrap');
$mail->addReplyTo('info@mailtrap.io', 'Mailtrap');
$mail->addAddress('recipient1@mailtrap.io', 'Tim'); 
$mail->addCC('cc1@example.com', 'Elena');
$mail->addBCC('bcc1@example.com', 'Alex');