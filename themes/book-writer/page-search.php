<?php
/**
 * Template Name: Search/Read
 *
 * Search page
 *
 */
?>
 <style>
    .btn,button{
        outline:none !important;
    }
    [type=checkbox].cross:checked+span:not(.lever):before{
        top: -3px;
        font-size: 22px;
        left: 2px;
        color: red;
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f00d";
        border: none !important;
        transform: rotate(0deg) !important;
    }
    .btn-include,.btn-exclude{
        background-color:transparent;
        color: #343434;
    }
    .btn-include.active,.btn-include:hover,.btn-include:focus{
        background-color:var(--theme-color);
        color:white;
    }
    .btn-exclude.active,.btn-exclude:hover,.btn-exclude:focus{
        background-color:#f44336;
        color:white;
    }
    .btn-clear:hover{
        color:white;
        background-color:black;
    }
    .btn-clear:focus{
        background-color:transparent;
        color:#343434;
    }
</style>
<?php
if (! isset($_SESSION)){
	session_start();
}
get_header();
$max_count = new WP_Query( array(
	'post_type'      => array( 'book' ),
	'meta_key'       => 'word-count',
	'orderby'        => 'meta_value_num',
	'posts_per_page' => 1,
	'order'          => 'DESC',
	'cache_results'  => true,
	'post__not_in'	 => get_stats_of('user_hidden',get_current_user_id()),
));
$max_count = get_post_meta( $max_count->posts[0]->ID, 'word-count', true );
?>
<span id="search_extras" style="display:none;"></span>
<span id="max_count" style="display:none;"><?php echo $max_count; ?></span>
<?php
$args = create_args_from_url();
$_SESSION["search_args"] = $args;
$default_query = new WP_Query( $args );
$original_query = $wp_query;
$wp_query = null;
$wp_query = $default_query;
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
	    <?php if (is_user_logged_in()){ ?>
          <ul class="collapsible">
            <li>
              <div class="collapsible-header">Saved Searches</div>
              <div class="collapsible-body">
                    <ul class="collection">
                      <a data-target="save_search_modal" disabled class="modal-trigger collection-item" style="font-weight:bold;border:none;">Save Current Search</a>
                    </ul>
                    <div class="row"></div>
                    <ul class="collection" id="searches-collection">
                        <?php
                            $searches = get_saved_searches();
                            foreach ($searches as $search){
                                ?><a href="<?php echo $search[1]; ?>" style="width:90%;display:inline-block;border:none;" class="collection-item"><?php echo $search[0]; ?></a><i onclick="delete_search('<?php echo $search[1]; ?>')" style="width:10%;text-align:center;font-size:19px;" class="btn-favorite far fa-trash-alt"></i><?php
                            }
                        ?>
                    </ul>
                  <!-- Modal Structure -->
                  <div id="save_search_modal" class="modal modal-mini">
                    <div class="modal-content" style="padding-bottom:0;">
                      <div class="row" style="margin-bottom:0;">
                        <div class="input-field col s12">
                          <input id="new_search_name" type="text" class="validate">
                          <label for="new_search_name">Search Name</label>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                        <a class="modal-close waves-effect btn-flat">Cancel</a>
                      <a onclick="save_current_search()" class="modal-close waves-effect btn-flat">Save</a>
                    </div>
                  </div>
              </div>
            </li>
          </ul>
	    <?php } ?>
		<button style="margin-bottom:50px;" class="mobile-margin waves-effect waves-light btn-small right" onclick="search_settings('open')">Filters</button>
		<div class="row" id="search-display"><div id="box" class="col s12">
			<?php
			if (have_posts()){
				// Start the loop.
				while (have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'search' );
					
					// End the loop.
				endwhile;
				
			}
			else {
				get_template_part( 'template-parts/content', 'noresult' );
			}
			?>
		</div></div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<div class="progress" style="display:none;margin:0;position: fixed;bottom: 0px;right: 0px;left: 0px;"><div class="indeterminate"></div></div>
<!-- Modal Structure -->
<div id="share_modal" class="modal">
<div class="modal-content">
  <h1 class="share_title">Share</h1>
  <div class="book_box">
  <h4 class="book_title_author"></h4>
  <p class="book_description"></p>
</div>
  <div class="share_icons">
    <a target="_blank" class="btn-hover btn-floating whatsapp_share"><i class="fab fa-whatsapp"></i></a>
    <a target="_blank" class="btn-hover btn-floating twitter_share"><i class="fab fa-twitter"></i></a>
    <a target="_blank" class="btn-hover btn-floating reddit_share"><i class="fab fa-reddit-alien"></i></a>
    <a target="_blank" class="btn-hover btn-floating facebook_share"><i class="fab fa-facebook-f"></i></a>
    <a class="btn-hover btn-floating copy_share"><i class="fas fa-link"></i></a>
  </div>
</div>
</div>
<?php get_template_part( 'template-parts/content', 'bookpaginate' ); ?>

<?php
	//Reset Data
	//This is because we dont want our meddling of the $wp-query to affect the whole site
	$wp_query = null;
	$wp_query = $original_query;
	wp_reset_postdata();
    get_template_part( 'template-parts/content', 'searchmodal' );
	get_footer();
?>

<script>
    function search_settings(method){
        if (method == 'open'){
            jQuery('#search-settings').css('display','block');
            jQuery('#search-display').css('display','none');
        }
        else if (method == 'close'){
            jQuery('#search-settings').css('display','none');
            jQuery('#search-display').css('display','block');
        }
    }
	function save_current_search(){
        var name = document.getElementById('new_search_name').value;
        var link = window.location.href.replace('https://fanfiction.online','');
        jQuery('.progress').css('display','block');
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/save_search.php',
			type: 'post',
			data: {ajax:1,name:name,link:link},
			success: function(response){
				jQuery('.progress').css('display','none');
				if (response == 3){
				    M.toast({html: 'Please login.'});
				}
				else if (response == 1){
				    M.toast({html: 'Search added.'});
				    jQuery('#searches-collection').append('<a href="' + link + '" style="width:90%;display:inline-block;border:none;" class="collection-item">' + name + '</a><i onclick="delete_search(\'' + link + '\')" style="width:10%;text-align:center;font-size:19px;" class="btn-favorite far fa-trash-alt"></i>');
				}
				else{
				    M.toast({html: response});
				}
			}
		});
    }
    function delete_search(link){
        jQuery('.progress').css('display','block');
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/save_search.php',
			type: 'post',
			data: {ajax:1,to_delete:'to_delete',link:link},
			success: function(response){
				jQuery('.progress').css('display','none');
				if (response == 3){
				    M.toast({html: 'Please login.'});
				}
				else if (response == 2){
				    M.toast({html: 'Search deleted.'});
				    var each = jQuery('#searches-collection').children('a');
                    for (var i = 0; i < each.length; i++) {
                        if (each[i].href.replace('https://fanfiction.online','') == link.replace('https://fanfiction.online','')){
                            jQuery(each[i]).next().remove();
                            jQuery(each[i]).remove();
                        }
                    }
				}
				
			}
		});
    }
</script>
