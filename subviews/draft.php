<?php
$draft = $app->draft;
$full_decode = json_decode($draft->content);
$content = $full_decode[0]->children[0]->text;
$substr = substr($content,0,200);
if (strlen($content) > 200 || count($full_decode) > 1 || count($full_decode[0]->children) > 1 ){
    $substr .= '...';
}
?>
<a href="/drafts/<?= $draft->ID; ?>/edit" class="draft grid-item">
    <updated><?= human_time_diff( intval($draft->updated) ); ?></updated>
    <draft-title><?= $draft->title; ?></draft-title>
    <excerpt><?= $substr; ?></excerpt>
</a>