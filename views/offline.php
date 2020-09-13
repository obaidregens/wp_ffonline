<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <title>You're offline</title>
    <style>
    body {
        font-family: 'Varela Round';
        font-size: 1.25em;
        margin: 3em 3em 0 3em;
    }
    </style>
    <script>setInterval(() => api("is_online",{callback: () => window.location.reload() }), 1000);</script>
</head>

<body>
    <h3>You're offline</h3>
    <p>Connect to the internet to continue.</p>
</body></html>