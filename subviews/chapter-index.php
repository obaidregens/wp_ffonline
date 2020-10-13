<?php
$all_chapters = published_chapters($app->story->ID);
?>
<style>
index > a[selected] {
    font-weight: bold;
    pointer-events: none;
}
</style>
<index>
    <li head>
        <cell>#</cell>
        <cell>Chapter</cell>
        <cell>Words</cell>
        <cell>Reviews</cell>
        <cell>Votes</cell>
    </li>
    <?php foreach ($all_chapters as $key => $link_chapter ) { ?>
        <a <?= intval($link_chapter->ID) === intval($app->selected_chapter ?? 0) ? "selected" : "" ?> href="<?= get_permalink( $link_chapter->ID ); ?>">
            <cell><?= $key+1; ?></cell>
            <cell><?= htmlspecialchars($link_chapter->post_title); ?></cell>
            <cell><?= get_post_meta($link_chapter->ID,'word-count',true); ?></cell>
            <cell><?= get_comments_number($link_chapter->ID); ?></cell>
            <cell><?= count(vote::query_by('chapter','type_id',$link_chapter->ID)); ?></cell>
        </a>
    <?php } ?>
</index>
