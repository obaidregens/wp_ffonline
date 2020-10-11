<?php
global $book_query;
$current_page = $book_query->args['page'];
?>
<a
<?php if ($current_page - 1 >= 1){ ?>
    href="
    <?= '?' . $book_query->get_url($current_page - 1) ?>"
    paginate="<?= $current_page - 1; ?>"
<?php } ?>
previous
></a>
<?php if ($current_page != 1){ ?>
    <a
        href="<?= '?' . $book_query->get_url(1) ?>"
        paginate="1"
    ></a>
    <a>...</a>
<?php } ?>
<a active><?= $current_page; ?></a>
<?php if ($current_page != $book_query->pages){ ?>
    <a>...</a>
    <a
        href="<?= '?' . $book_query->get_url($book_query->pages) ?>"
        paginate="<?= $book_query->pages; ?>"
    ></a>
<?php } ?>
<a
<?php if ($current_page + 1 <= $book_query->pages){ ?>
    href="
    <?= '?' . $book_query->get_url($current_page + 1); ?>"
    paginate="<?= $current_page + 1; ?>"
<?php } ?>
next
></a>