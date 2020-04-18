<div class="row">
	<div class="col s12" id="pagination-wrapper">
		<ul class="paginationm center-align" style="margin:0;width:auto;">
		    <?php if (isset($_GET['page']) && intval($_GET['page']) > 1){ ?>
    			<li class="waves-effect"><a onclick="paginate(<?php echo intval($_GET['page'])-1; ?>)"><i class="material-icons">chevron_left</i></a></li>
    			<li class="waves-effect"><a onclick="paginate(1)">1</a></li>
    			<li class="disabled"><a>...</a></li>
    			<li class="active disabled"><a><?php echo intval($_GET['page']); ?></a></li>
    			<li class="disabled"><a>...</a></li>
    			<li class="waves-effect"><a onclick="paginate(<?php echo $wp_query->max_num_pages;?>)"><?php echo $wp_query->max_num_pages;?></a></li>
    			<li class="waves-effect"><a onclick="paginate(<?php echo intval($_GET['page'])+1; ?>)"><i class="material-icons">chevron_right</i></a></li>		        
		    <?php } else { ?>
    			<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>
    			<li class="active disabled"><a>1</a></li>
    			<li class="disabled"><a>...</a></li>
    			<li class="waves-effect"><a onclick="paginate(<?php echo $wp_query->max_num_pages;?>)"><?php echo $wp_query->max_num_pages;?></a></li>
    			<li class="waves-effect"><a onclick="paginate(2)"><i class="material-icons">chevron_right</i></a></li>
		    <?php } ?>
		</ul>
	</div>
</div>

<script type = "text/javascript">
	function paginate(to){
		document.getElementById('box').innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
		document.getElementById('pagination-wrapper').innerHTML = '';
		jQuery("#box").addClass("center-align");
		jQuery("#search-btn").addClass("disabled");
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/search.php',
			type: 'post',
			data: {ajax: 1,page:to},
			success: function(response){
				jQuery("#box").removeClass("center-align");
				jQuery("#search-btn").removeClass("disabled");
				var response_arr  = response.split('<div id="split_max_pages_count"></div>');
				var after = '<li class="disabled"><a>...</a></li><li class="waves-effect"><a onclick="paginate(' + response_arr[0] + ')">' + response_arr[0] + '</a></li>';
				var before = '<li class="waves-effect"><a onclick="paginate(1)">1</a></li><li class="disabled"><a>...</a></li>';
				if (to == 1){
					var prev_class = ' class="disabled" ';
					var next_class = ' class="waves-effect" onclick="paginate(' + (to + 1) + ')" ';
					before = '';
				}
				else if(to == response_arr[0]){
					var prev_class = ' class="waves-effect" onclick="paginate(' + (to - 1) + ')" ';
					var next_class = ' class="disabled" ';
					after = '';
				}
				else{
					var prev_class = ' class="waves-effect" onclick="paginate(' + (to - 1) + ')" ';
					var next_class = ' class="waves-effect" onclick="paginate(' + (to + 1) + ')" ';
				}
				document.getElementById('pagination-wrapper').innerHTML = '<ul class="paginationm center-align"><li><a' + prev_class + '><i class="material-icons">chevron_left</i></a></li>' + before + '<li class="active disabled"><a>' + to + '</a></li>' + after + '<li><a' + next_class + '><i class="material-icons">chevron_right</i></a></li></ul>';
				document.getElementById('box').innerHTML = response_arr[1];
				
                var url = new URL(window.location);
                url.searchParams.set('page', to);
                window.history.pushState("object or string", document.getElementsByTagName("title")[0].innerHTML,url.href);
			}
		});
	}
</script>