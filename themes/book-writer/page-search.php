<?php
/**
 * Template Name: Search/Read
 *
 * Search page
 *
 */
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
		<?php get_template_part('template-parts/create','search'); ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php
	get_footer();
?>

<script>
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
