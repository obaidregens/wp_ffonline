<?php
global $wp_query;
if (isset($wp_query->query['paged'])){
    $current_page = $wp_query->query['paged'];
}
else{
    $current_page = 1;
}
?>
<div class="row">
	<div class="col s12">
		<ul class="paginationm center-align" style="margin:0;width:auto;">
			<li class="waves-effect"><a <?php if ($current_page - 1 >= 1){ ?> onclick="paginate(<?php echo $current_page - 1; ?>)" <?php } ?> ><i class="material-icons">chevron_left</i></a></li>
            <?php if ($current_page != 1){ ?>
                <li class="waves-effect"><a onclick="paginate(1)">1</a></li>
                <li class="disabled"><a>...</a></li>
            <?php } ?>
			<li class="active disabled"><a><?php echo $current_page; ?></a></li>
            <?php if ($current_page != $wp_query->max_num_pages){ ?>
                <li class="disabled"><a>...</a></li>
                <li class="waves-effect"><a onclick="paginate(<?php echo $wp_query->max_num_pages; ?>)"><?php echo $wp_query->max_num_pages;?></a></li>
            <?php } ?>
			<li class="waves-effect"><a <?php if ($current_page + 1 <= $wp_query->max_num_pages){ ?> onclick="paginate(<?php echo $current_page + 1; ?>)" <?php } ?> ><i class="material-icons">chevron_right</i></a></li>	
		</ul>
	</div>
</div>