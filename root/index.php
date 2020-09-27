<?php
function handle_all_breaking_errors($a = 1) {
    if (!in_array($a,[1,4,16,64,256,4096])) {
        return;
    }
    http_response_code(503);
    header("x-is-still-in-temp: true");
    ?>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
        <title>Just a few seconds</title>
        <style>
        body {
            font-family: 'Varela Round';
            font-size: 1.25em;
            margin: 3em 3em 0 3em;
        }
        </style>
        <script>
        setInterval(() => fetch('/').then((res) => {
            const isT = res.headers.has('x-is-still-in-temp');
            if (! isT) {
                window.location.reload();
            }
        }), 1000);</script>
    </head>
    
    <body>
        <h3>Just a few seconds</h3>
        <p>This should only take a few seconds. You'll be redirected automatically.</p>
    </body></html>
    <?php
    exit();
}
set_error_handler ( 'handle_all_breaking_errors' );
if (!file_exists(__DIR__ . '/content/index.php')) {
    handle_all_breaking_errors();
}
require_once(__DIR__ . '/content/index.php');