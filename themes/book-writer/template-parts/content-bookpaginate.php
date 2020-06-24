<?php
global $book_query;
$current_page = $book_query->args['page'];
$current_get = $_GET;
?>
<style>
    .paginationm a[href]{
        display: none;
    }
</style>
<div class="row">
	<div class="col s12">
		<ul class="paginationm center-align" style="margin:0;width:auto;">
			<li class="waves-effect">
                <?php if ($current_page - 1 >= 1){ ?>
                    <a href="<?php $current_get['page'] = $current_page - 1; echo '/?' . http_build_query($current_get) ?>"></a>
                    <a onclick="paginate(<?php echo $current_page - 1; ?>)"><i class="material-icons">chevron_left</i></a>
                <?php } else { ?>
                    <a><i class="material-icons">chevron_left</i></a>
                <?php } ?>
            </li>
            <?php if ($current_page != 1){ ?>
                <li class="waves-effect">
                    <a href="<?php $current_get['page'] = 1; echo '/?' . http_build_query($current_get) ?>"></a>
                    <a onclick="paginate(1)">1</a>
                </li>
                <li class="disabled"><a>...</a></li>
            <?php } ?>
			<li class="active disabled"><a><?php echo $current_page; ?></a></li>
            <?php if ($current_page != $book_query->pages){ ?>
                <li class="disabled"><a>...</a></li>
                <li class="waves-effect">
                    <a href="<?php $current_get['page'] = $book_query->pages; echo '/?' . http_build_query($current_get) ?>"></a>
                    <a onclick="paginate(<?php echo $book_query->pages; ?>)"><?php echo $book_query->pages;?></a>
                </li>
            <?php } ?>
			<li class="waves-effect">
                <?php if ($current_page + 1 <= $book_query->pages){ ?>
                    <a href="<?php $current_get['page'] = $current_page + 1; echo '/?' . http_build_query($current_get) ?>"></a>
                    <a onclick="paginate(<?php echo $current_page + 1; ?>)"><i class="material-icons">chevron_right</i></a>
                <?php } else { ?>
                    <a><i class="material-icons">chevron_right</i></a>
                <?php } ?>
            </li>	
		</ul>
	</div>
</div>