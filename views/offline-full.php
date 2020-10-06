<?php
header("x-is-serving-offline: true");
?>
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
        background-color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#121212" : "#fdfdfd"?>;
        color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#b7bfc4" : "#262828"?>;
    }
    h4 {
        margin-bottom: 10px;
    }
    stories-list > a * {
        pointer-events: none;
    }
    story-meta {
        color: grey;
        font-size: 0.9rem;
    }
    story-author {
        font-size: 1.1rem;
    }
    story-title {
        font-size: 1.6rem;
        font-weight: bold;
    }
    stories-list,stories-list > a {
        display: flex;
        flex-direction: column;
        transition: box-shadow .2s;
        cursor: pointer;
    }
    stories-list:empty::after {
        content: "You have no offline stories";
        font-size: 1rem;
        margin-left: 0px;
    }
    stories-list > a {
        padding: 15px;
    }
    stories-list > a:hover {
        box-shadow: 0 0 2px 0 rgb(128 128 128 / 12%),0 4px 16px 0 rgb(128 128 128 / 12%);
    }
    single:not(:last-child)::after {
        content: ' - ';
    }
    </style>
    <script>
    const intID = setInterval(() => fetch('/').then((res) => {
        const isT = res.headers.has('x-is-serving-offline');
        if (! isT) {
            clearInterval(intID);
            setTimeout(() => {
                // window.location.reload();
            }, 3000);
        }
    }), 1000);
    window.addEventListener('load',() => {
        const stories_list = document.createElement('stories-list');
        const offline_stories = JSON.parse(localStorage.getItem('offline_stories')) || {};
        Object.entries(offline_stories).forEach(([i,v]) => {
            const time = new Date(v.time_added);
            let time_formatted = (time.toString()).split(' GMT')[0];
            time_formatted = time_formatted.substring(0,time_formatted.length-3);
            const el = document.createElement('a');
            el.addEventListener('click',() => window.location.href = "/story/" + i)
            el.innerHTML =`
            <story-title>${v.title}</story-title>
            <story-author>by ${v.author}</story-author>
            <story-meta>
                <single>${v.chapters} Chapters</single>
                <single>Added ${time_formatted}</single>
            </story-meta>
            `;
            stories_list.appendChild(el);
        });
        document.querySelector('body').appendChild(stories_list);
    });
    </script>
</head>

<body>
    <h3>You're offline</h3>
    <p>Connect to the internet to continue.</p>
    <h4>Offline Stories</h4>
</body>
</html>