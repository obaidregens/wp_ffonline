<style>
pre {
    background-color: transparent;
    outline: none !important;
    border:1px solid;
    padding: 25px;
    border-radius: 5px;
    margin: 20px;
    user-select: all;
    color: var(--text-color);
    cursor: pointer;
    white-space: pre-line;
}
</style>
<h3>Export to AO3</h3>
<ol>
<li>Click on the HTML below to copy.</li>
<li>Go to Post -> New Work</li>
<li>Scroll to "Work Text" at the bottom and paste HTML there.</li>
</ol>
<pre onclick="window.expose.copyText(this.innerText,true);" ><?= htmlspecialchars(drafts_json::output_html($app->draft->content)); ?></pre>