<?php
$all_chapters = published_chapters($app->story->ID);
?>
<chapter-index>
    <?php foreach ($all_chapters as $key => $link_chapter ) { ?>
        <a <?= intval($link_chapter->ID) === intval($app->selected_chapter ?? 0) ? "selected" : "" ?> href="<?= get_permalink( $link_chapter->ID ); ?>">
            <chapter-title><?= $key+1; ?>. <?= htmlspecialchars($link_chapter->post_title); ?></chapter-title>
            <chapter-meta>
                <span tooltip-top="Words"><?= get_post_meta($link_chapter->ID,'word-count',true); ?></span>
                <span tooltip-top="Votes"><?= count(vote::query_by('chapter','type_id',$link_chapter->ID)); ?></span>
                <span tooltip-top="Reviews"><?= get_comments_number($link_chapter->ID); ?></span>
            </chapter-meta>
        </a>
    <?php } ?>
</chapter-index>