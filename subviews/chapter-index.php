<?php
$all_chapters = published_chapters($app->story->ID);
?>
<index>
    <li head>
        <cell>#</cell>
        <cell>Chapter</cell>
        <cell>Words</cell>
        <cell>Reviews</cell>
    </li>
    <?php foreach ($all_chapters as $key => $link_chapter ) { ?>
        <a href="<?= get_permalink( $link_chapter->ID ); ?>">
            <cell><?= $key+1; ?></cell>
            <cell><?= $link_chapter->post_title; ?></cell>
            <cell><?= get_post_meta($link_chapter->ID,'word-count',true); ?></cell>
            <cell><?= get_comments_number($link_chapter->ID); ?></cell>
        </a>
    <?php } ?>
</index>
