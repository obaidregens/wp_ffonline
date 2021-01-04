<?php
global $book_query;
global $book;
$search = $book_query->args['search'];
$chapters_count = $book_query->book_metas[$book->ID]['chapters'];
$track = track_reading::get($book->ID);
$track_percentage = $track ? round($track['num']/$chapters_count*100,1) : false;
?>
<div class="swiper-container story-single waves-effect">
    <div class="swiper-wrapper">
        <a link="<?= home_url('/story/' . $book->ID . '/1'); ?>" href="<?= '/story/' . $book->ID . '/' . ($track ? $track['num'].'#'.$track["para"] : '1'); ?>" class="book swiper-slide" book_id="<?= $book->ID; ?>" chapters="<?= $chapters_count; ?>" >
            <object type="invalid/mime"><a class="more waves-effect"></a></object>
            <span class="title"><?= mark_search(htmlspecialchars($book->post_title), $search); ?></span>
            <span class="author"><object type="invalid/mime"><?= author_href($book->ID); ?></object></span>
            <div class="description"><?= mark_search(htmlspecialchars($book->post_excerpt), $search); ?></div>
            <?php print_book_meta($book->ID,$book_query); ?>
            <object type="invalid/mime"><?php print_book_tags($book->ID,$book_query); ?></object>
            <reading-progress <?= !$track ? "" : "style='--perc: $track_percentage%'" ?>></reading-progress>
        </a>
        <div class="swiper-slide">
            <options>
                <li class="waves-effect" tabindex="0" label="Favorite"></li>
                <li class="waves-effect" tabindex="0" label="Share"></li>
                <li class="waves-effect" tabindex="0" label="Offline"></li>
            </options>
        </div>
    </div>
</div>