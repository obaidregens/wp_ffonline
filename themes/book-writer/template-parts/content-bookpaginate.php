<?php
global $book_query;
$current_page = $book_query->args['page'];
$current_get = $_GET;
?>
<a
<?php if ($current_page - 1 >= 1){ ?>
    href="
    <?php
        $current_get['page'] = $current_page - 1;
        echo '/?' . http_build_query($current_get)
    ?>"
    paginate="<?= $current_get['page']; ?>"
<?php } ?>
previous
></a>
<?php if ($current_page != 1){ ?>
    <a
        href="<?php $current_get['page'] = 1; echo '/?' . http_build_query($current_get) ?>"
        paginate="1"
    ></a>
    <a>...</a>
<?php } ?>
<a active><?php echo $current_page; ?></a>
<?php if ($current_page != $book_query->pages){ ?>
    <a>...</a>
    <a
        href="<?php $current_get['page'] = $book_query->pages; echo '/?' . http_build_query($current_get) ?>"
        paginate="<?= $book_query->pages; ?>"
    ></a>
<?php } ?>
<a
<?php if ($current_page + 1 <= $book_query->pages){ ?>
    href="
    <?php
        $current_get['page'] = $current_page + 1;
        echo '/?' . http_build_query($current_get);
    ?>"
    paginate="<?= $current_get['page']; ?>"
<?php } ?>
next
></a>