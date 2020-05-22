<?php
/**
 * Template Name: Search/Read
 *
 * Search page
 *
 */

global $vfs;
$vfs = vfs();
log_stats('view','home',$post->ID,$vfs);
$front_cache = get_front_cache();
if (! is_null($front_cache) && !is_user_logged_in() && empty($_GET)){
	//Find Args from Url
	$args = unpack_search($_GET);
	$search_id = log_search($args);
	$replaced = preg_replace('~(<span id="search_id" style="display:none;">)(([0-9]+)|())(</span>)~','<span id="search_id" style="display:none;">' . $search_id . '</span>',$front_cache);
  echo $replaced;
  exit();
}
get_header();
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
                                ?><a href="<?php echo packed_to_url(pack_search($search[1])); ?>" style="width:90%;display:inline-block;border:none;" class="collection-item"><?php echo $search[0]; ?></a><i onclick="delete_search('<?php echo $search[1]; ?>')" style="width:10%;text-align:center;font-size:19px;" class="btn-favorite far fa-trash-alt"></i><?php
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
		<?php get_template_part('template-parts/create','search'); ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
	get_footer();
?>

<script>
	function save_current_search(){
        var name = document.getElementById('new_search_name').value;
        var id = document.getElementById('search_id').innerHTML;
        jQuery('.progress').css('display','block');
    		jQuery.ajax({
    			url: '/wp-content/themes/book-writer/php/save_search.php',
    			type: 'post',
    			data: {ajax:1,name:name,id:id},
    			success: function(response){
    				jQuery('.progress').css('display','none');
    				if (response == 3){
    				    M.toast({html: 'Please login.'});
    				}
    				else if (response == 1){
    				    M.toast({html: 'Search added.'});
                window.location.reload();
    				}
    				else{
    				    M.toast({html: response});
    				}
    			}
    		});
    }
    function delete_search(id){
        jQuery('.progress').css('display','block');
    		jQuery.ajax({
    			url: '/wp-content/themes/book-writer/php/save_search.php',
    			type: 'post',
    			data: {ajax:1,to_delete:'to_delete',id:id},
    			success: function(response){
    				jQuery('.progress').css('display','none');
    				if (response == 3){
    				    M.toast({html: 'Please login.'});
    				}
    				else if (response == 2){
    				    M.toast({html: 'Search deleted.'});
                window.location.reload();
    				}	
            else{
              
            }
    			}
    		});
    }
</script>
