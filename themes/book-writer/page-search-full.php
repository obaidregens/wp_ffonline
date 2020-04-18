<?php
get_header();
if (current_user_can('administrator')){
	if(!isset($_SESSION)) 
	{ 
		session_start(); 
	}
	$args = array(
		'post_type'      		 => array( 'book' ),
		'post_status'            => array( 'publish' ),
		'posts_per_page' 		 => 10,
		'order'                  => 'DESC',
		'orderby'                => 'modified',
	);
	$_SESSION["search_args"] = $args;
	$default_query = new WP_Query( $args );
	$original_query = $wp_query;
	$wp_query = null;
	$wp_query = $default_query;
	?>
    <div id="primary" class="content-area">
    	<main id="main" class="site-main mobile-margin" role="main">
			<button style="margin-bottom:50px;" class="waves-effect waves-light modal-trigger btn-small right" href="#search-settings">Filters</button>
			<div class="row"><div id="box" class="col s12">
				<?php
					// Start the loop.
					while (have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', 'search' );
						// End the loop.
					endwhile;
				?>
			</div></div>
    	</main><!-- .site-main -->
    </div><!-- .content-area -->
	<div class="row">
		<div class="col s12" id="pagination-wrapper">
			<ul class="paginationm center-align">
				<li class="disabled"><a><i class="material-icons">chevron_left</i></a></li>
				<li class="active disabled"><a>1</a></li>
				<li class="disabled"><a>...</a></li>
				<li class="waves-effect" onclick="paginate(<?php echo $wp_query->max_num_pages;?>)"><a><?php echo $wp_query->max_num_pages;?></a></li>
				<li class="waves-effect" onclick="paginate(2)"><a><i class="material-icons">chevron_right</i></a></li>
			</ul>
		</div>
	</div>
	<?php
		//Reset Data
		//This is because we dont want our meddling of the $wp-query to affect the whole site
		$wp_query = null;
		$wp_query = $original_query;
		wp_reset_postdata();
	
		get_sidebar();
		get_footer();
		get_template_part( 'template-parts/content', 'searchmodal' );
		$max_count = new WP_Query( array(
			'post_type'      => array( 'book' ),
			'meta_key'       => 'word-count',
			'orderby'        => 'meta_value_num',
			'posts_per_page' => 1,
			'order'          => 'DESC',
			'cache_results'  => true,
		) );
		$max_count = get_post_meta( $max_count->posts[0]->ID, 'word-count', true );
	?>
	<span id="max_count" style="display:none;"><?php echo $max_count; ?></span>
	<script type = "text/javascript">
		jQuery("form[name='search']").submit(function(event) {
			event.preventDefault();

			document.getElementById('box').innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
			document.getElementById('pagination-wrapper').innerHTML = '';
			jQuery("#box").addClass("center-align");
			jQuery("#search-btn").addClass("disabled");
			var search = jQuery('#search').val();
			var rating = jQuery('#rating').val();
			var language = jQuery('#language').val();
			var status = jQuery('#status').val();
			var genre = jQuery('#genre').val();
			var character = jQuery('#character').val();
			var pairing = jQuery('#pairing').val();
			var words = document.getElementById('words-slider');
			words = slider.noUiSlider.get();
			jQuery.ajax({
				url: '/wp-content/themes/book-writer/php/search.php',
				type: 'post',
				data: {ajax: 1,search:search,rating:rating,language:language,status:status,genre:genre,character:character,pairing:pairing,words:words},
				success: function(response){
					jQuery("#box").removeClass("center-align");
					jQuery("#search-btn").removeClass("disabled");
					var response_arr  = response.split('<div id="split_max_pages_count"></div>');
					var next_new = '<li class="disabled"><a>...</a></li><li class="waves-effect" onclick="paginate(' + response_arr[0] + ')"><a>' + response_arr[0] + '</a></li><li class="waves-effect" onclick="paginate(2)"><a><i class="material-icons">chevron_right</i></a></li>';
					if (response_arr[0] == 1){
						next_new = '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>'
					}
					document.getElementById('pagination-wrapper').innerHTML = '<ul class="paginationm center-align"><li class="disabled"><a><i class="material-icons">chevron_left</i></a></li><li class="active disabled"><a>1</a></li>' + next_new + '</ul>';
					document.getElementById('box').innerHTML = response_arr[1];
				}
			});
		});
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
					var after = '<li class="disabled"><a>...</a></li><li class="waves-effect" onclick="paginate(' + response_arr[0] + ')"><a>' + response_arr[0] + '</a></li>';
					var before = '<li class="waves-effect" onclick="paginate(1)"><a>1</a></li><li class="disabled"><a>...</a></li>';
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
					document.getElementById('pagination-wrapper').innerHTML = '<ul class="paginationm center-align"><li' + prev_class + '><a><i class="material-icons">chevron_left</i></a></li>' + before + '<li class="active disabled"><a>' + to + '</a></li>' + after + '<li' + next_class + '><a><i class="material-icons">chevron_right</i></a></li></ul>';
					document.getElementById('box').innerHTML = response_arr[1];
				}
			});
		}
	</script>
	<style>
		.noUi-tooltip span {
			width: fit-content;
			text-align: center;
			color: black;
			font-size: 12px;
			opacity: 0;
			position: absolute;
			top: 40px;
			left: -50px;
			transition: opacity .25s cubic-bezier(.215,.61,.355,1);
		}
	</style>
	<?php
}
else{
	?><title>Page not found - Fanfiction Online</title><?php
	get_template_part('404');

}