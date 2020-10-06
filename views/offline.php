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
    <style>body{font-family:'Varela Round';font-size:1.25em;margin:3em 3em 0 3em;background-color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#121212" : "#fdfdfd"?>;color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#b7bfc4" : "#262828"?>}h4{margin-bottom:10px}stories-list > a * {pointer-events:none;}story-meta{color:grey;font-size:.9rem}story-author{font-size:1.1rem}story-title{font-size:1.6rem;font-weight:700}stories-list,stories-list>a{display:flex;flex-direction:column;transition:box-shadow .2s;cursor:pointer}stories-list:empty::after{content:"You have no offline stories";font-size:1rem;margin-left:0}stories-list>a{padding:15px}stories-list>a:hover{box-shadow:0 0 2px 0 rgb(128 128 128 / 12%),0 4px 16px 0 rgb(128 128 128 / 12%)}single:not(:last-child)::after{content:' - '}</style>
    <script>const intID=setInterval(()=>fetch("/").then(e=>{e.headers.has("x-is-serving-offline")||(clearInterval(intID),setTimeout(()=>{},3e3))}),1e3);window.addEventListener("load",()=>{const e=document.createElement("stories-list"),t=JSON.parse(localStorage.getItem("offline_stories"))||{};Object.entries(t).forEach(([t,n])=>{let s=new Date(n.time_added).toString().split(" GMT")[0];s=s.substring(0,s.length-3);const r=document.createElement("a");r.addEventListener("click",()=>window.location.href="/story/"+t),r.innerHTML=`\n<story-title>${n.title}</story-title>\n<story-author>by ${n.author}</story-author>\n<story-meta>\n<single>${n.chapters} Chapters</single>\n<single>Added ${s}</single>\n</story-meta>\n`,e.appendChild(r)}),document.querySelector("body").appendChild(e)});</script>
</head>
<body>
    <h3>You're offline</h3>
    <p>Connect to the internet to continue.</p>
    <h4>Offline Stories</h4>
</body>
</html>