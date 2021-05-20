<?php
class RequestPOST {
    function __construct(
        string $url,
        array $data,
        array $headers = [],
        string $content_type = "application/json"
    ){
        $this->url = $url;
        $this->data = $data;
        $this->headers = $headers;
        $this->content_type = $content_type;
    }
    function send() {
        $options = [
            'http' => [
                'header'  => array_merge([
                    "Content-Type: {$this->content_type}",
                ],$this->headers),
                'method'  => "POST",
                'content' => http_build_query($this->data)
            ]
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($this->url, false, $context);
        if ($result === FALSE) { 
            return false;
        }
        return true;
    }
}


class SendGrid {
    protected static $template_ids = [
        "change-email"  => "d-769c318c0d5a44169cc8a65e59df235d",
        "faq-alert"     => "d-2eb9056e89c84142ba9e85a2111fbb8a",
        "notification"  => "d-1dce9206175c405a8a2a12a19d09f717",
        "contact"       => "d-8abd9623b6bf47a68bcacd3e3833107d",
        "contact-first" => "d-e42295a0ed2b4b3697536946530bb9b9",
        "OTP"           => "d-889f8f688a9d4873a85938c6b3fa937e",
        "signup"        => "d-59f6fbddc5d049438c6dbf7156fa668b"
    ];
    function __construct(string $template, array $params = [], string $from = SENDGRID_noreply) {
        $this->from = $from;
        $this->template = $template;
        $this->$params = $params;
    }
    function send(array $to) {
        new RequestPOST("https://api.sendgrid.com/v3/mail/send",[
            "from" => [
                "email" => $this->from,
            ],
            "personalizations" => [
                "to" => [
                    ["email"    => $to]
                ],
                "dynamic_template_data" => $this->params
            ],
            "template_id" => self::$template_ids[$this->template]
        ],[
            "Authorization: Bearer" => SENDGRID_APIKEY
        ]);
    }
}