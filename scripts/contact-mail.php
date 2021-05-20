<?php
assert_options(ASSERT_BAIL,1);

assert($_GET["secret"] === "jnqdit9drxetnrkbosew5slfp9t9j0e63idsl3uj");

assert(filter_var($_POST['to'], FILTER_VALIDATE_EMAIL));
assert(filter_var($_POST['from'], FILTER_VALIDATE_EMAIL));

$messageid = "";
foreach (explode("\n",$_POST["headers"]) as $line) {
    $h = explode(": ",$line,2);
    if (strtolower($h[0]) === "message-id") {
        $messageid = $h[1];
    }
}
for ($i=0; $i < explode("\n",$_POST["headers"]); $i++) { 
    
}

global $wpdb;
$wpdb->insert(
    'contact',
    [
        'user_id'       => 0,
        'from'          => $_POST["from"],
        'to'            => $_POST['to'],
        'received_time' => microtime(true),
        'subject'       => $_POST["subject"],
        'headers'       => $_POST["headers"],
        'message'       => $_POST["html"],
        'message_id'    => $messageid,
        'vfs'           => "",
    ]
);