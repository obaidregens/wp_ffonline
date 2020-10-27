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
    <style>body{font-family:'Varela Round';font-size:1.25em;margin:3em 3em 0 3em;background-color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#121212" : "#fdfdfd"?>;color:<?= (($_COOKIE['theme'] ?? 'light') === 'dark') ? "#b7bfc4" : "#262828"?>}h4{margin-bottom:10px}stories-list>a *{pointer-events:none}story-meta{color:grey;font-size:.9rem}story-author{font-size:1.1rem}story-title{font-size:1.6rem;font-weight:700}stories-list,stories-list>a{display:flex;flex-direction:column;transition:box-shadow .2s;cursor:pointer}stories-list:empty::after{content:"You have no offline stories";font-size:1rem;margin-left:0}stories-list>a{padding:15px}stories-list>a:hover{box-shadow:0 0 2px 0 rgb(128 128 128 / 12%),0 4px 16px 0 rgb(128 128 128 / 12%)}single:not(:last-child)::after{content:' - '}</style>
    <script>var idbKeyval=function(e){"use strict";class t{constructor(e="keyval-store",t="keyval"){this.storeName=t,this._dbp=new Promise((r,n)=>{const o=indexedDB.open(e,1);o.onerror=(()=>n(o.error)),o.onsuccess=(()=>r(o.result)),o.onupgradeneeded=(()=>{o.result.createObjectStore(t)})})}_withIDBStore(e,t){return this._dbp.then(r=>new Promise((n,o)=>{const s=r.transaction(this.storeName,e);s.oncomplete=(()=>n()),s.onabort=s.onerror=(()=>o(s.error)),t(s.objectStore(this.storeName))}))}}let r;function n(){return r||(r=new t),r}return e.Store=t,e.get=function(e,t=n()){let r;return t._withIDBStore("readonly",t=>{r=t.get(e)}).then(()=>void 0===r.result?null:r.result)},e.set=function(e,t,r=n()){return r._withIDBStore("readwrite",r=>{r.put(t,e)})},e.del=function(e,t=n()){return t._withIDBStore("readwrite",t=>{t.delete(e)})},e.clear=function(e=n()){return e._withIDBStore("readwrite",e=>{e.clear()})},e.keys=function(e=n()){const t=[];return e._withIDBStore("readonly",e=>{(e.openKeyCursor||e.openCursor).call(e).onsuccess=function(){this.result&&(t.push(this.result.key),this.result.continue())}}).then(()=>t)},e}({});const intID=setInterval(()=>fetch("/").then(e=>{e.headers.has("x-is-serving-offline")||(clearInterval(intID),setTimeout(()=>{window.location.reload()},3e3))}),1e3);window.addEventListener("load",async()=>{const e=document.createElement("stories-list"),t=JSON.parse(await idbKeyval.get("offline_stories"))||{stories:{}};Object.entries(t.stories).forEach(([t,r])=>{let n=new Date(r.time_added).toString().split(" GMT")[0];n=n.substring(0,n.length-3);const o=document.createElement("a");o.addEventListener("click",()=>window.location.href="/story/"+t),o.innerHTML=`<story-title>${r.title}</story-title><story-author>by ${r.author}</story-author><story-meta><single>${r.chapters} Chapters</single><single>Added ${n}</single></story-meta>`,e.appendChild(o)}),DOM.q("body").appendChild(e)});</script>
</head>

<body>
    <h3>You're offline</h3>
    <p>Connect to the internet to continue.</p>
    <h4>Offline Stories</h4>
</body>
</html>