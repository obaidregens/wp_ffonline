<?php
$ends = new DateTime( );
$stamp = intval($app->last_beta->end_time);
$ends->setTimestamp( $stamp/1000 );
$formatted = $ends->format("l jS F Y") . " at " . $ends->format("g:i A e");
?>
<h1>Sorry 😬</h1>
<h2>Your invite expired <time stamp="<?=$stamp; ?>"><?= $formatted ?></time></h2>
<a href="https://fanfiction.online<?= $app->request; ?>">Switch to main site</a>

<script>
window.addEventListener('load',() => {
    const time_el = DOM.q('time');
    time_el.innerText = _t.local(new Date(parseInt(time_el.getAttribute('stamp'))))
});
</script>