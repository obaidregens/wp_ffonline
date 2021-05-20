<?php
class RequestPOST {
    function __construct($url,$data,$content_type = "application/json"){
        $this->url = $url;
        $this->data = $data;
        $this->content_type = $content_type;
    }
    function send() {
        $options = [
            'http' => [
                'header'  => [
                    "Content-type: {$this->content_type}",
                    
                ],
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
        ]);
    }
}