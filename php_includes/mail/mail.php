<?php
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

function email(array $args) {
    $mail = new email($args);
    $mail->send($args['to']);
    $mail->close();
}

class email {
    protected $from;
    protected $template = false;
    protected $mail;
    function __construct(array $args) {
        $this->subject = $args['subject'] ?? 'Message from Fanfiction Online';
        $this->params = $args['params'] ?? [];
        $this->txtparams = $args['txtparams'] ?? [];
        $this->htmlparams = $args['htmlparams'] ?? [];
        // Mailer
        $this->createMailer();
        $this->setFrom($args['from'] ?? 'noreply');
        $this->setReply($args['reply-to'] ?? '');
        $this->setTemplate($args['template'] ?? '');
    }
    protected function createMailer() {
        $this->mail = new PHPMailer(true);
        $this->mail->CharSet="UTF-8";
        $this->mail->isSMTP();
        $this->mail->Host = defined("SMTP_HOST") ? SMTP_HOST : "";
        $this->mail->SMTPAuth = defined("SMTP_AUTH") ? SMTP_AUTH : true;
        $this->mail->Port = defined("SMTP_PORT") ? SMTP_PORT : 0;
        $this->mail->SMTPSecure = defined("SMTP_SECURE") ? SMTP_SECURE : "tls";
        $this->mail->SMTPKeepAlive = true;
    }
    function setTemplate($template) {
        ob_start();
        $html_template = file_get_contents(__DIR__ . '/templates/' . $template . '.html');
        $txt_template = file_get_contents(__DIR__ . '/templates/' . $template . '.txt');
        ob_end_clean();
        if ( $html_template === false || $txt_template === false ){
            return false;
        }
        foreach ($this->htmlparams as $k => $v) {
            $html_template = str_replace($k,$v,$html_template);
        }
        foreach ($this->txtparams as $k => $v) {
            $txt_template = str_replace($k,$v,$txt_template);
        }
        foreach ($this->params as $k => $v) {
            $html_template = str_replace($k,$v,$html_template);
            $txt_template = str_replace($k,$v,$txt_template);
        }    
        $this->template = $template;
        $this->mail->isHTML(true);
        $this->mail->Body = $html_template;
        $this->mail->AltBody = $txt_template;
    }
    function setReply($mailId) {
        $this->mail->clearCustomHeaders();
        if (trim($mailId) !== "") {
            $this->mail->addCustomHeader('References', $mailId);
            $this->mail->addCustomHeader('In-Reply-To', $mailId);    
        }
    }
    function setFrom($from) {
        global $email_creds;
        $this->from = $email_creds[$from] ?? null;
        if ($this->from === null) {
            return false;
        }
        $this->mail->Username = $this->from['email'];
        $this->mail->Password = $this->from['pass'];
        $this->mail->setFrom($this->from['email'], $this->from['name']);
        $this->mail->addReplyTo($this->from['email'], $this->from['name']);
    }
    function send($to) {
        $to = (array) $to;
        $this->mail->Subject = $this->subject;
        foreach ($to as $to_email ) {
            $this->mail->clearAddresses();
            $this->mail->addAddress( $to_email );
            $this->mail->send();
        }
        return $this->mail->getLastMessageID();
    }
    function close() {
        $this->mail->SmtpClose();
    }
}