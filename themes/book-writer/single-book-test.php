<?php
/**
* The template for displaying book pages
* Template Name: Book
*
**/
?>
<style>
#wpadminbar{
    display:none;
}
.btn-hover.active{
    background-color: rgba(0,0,0,.05)!important;
}
.btn-hover:focus{
    background-color: transparent !important;
}
</style>
<?php
//page_header
get_header();
if (isset($_GET['chapter']) && is_numeric($_GET['chapter'])){
	$chapter = new WP_Query( array(
		'post_type'      => array( 'chapter' ),
		'post_parent'    => $post->ID,
		'meta_key'       => 'chapter_order',
		'meta_value' => intval($_GET['chapter']),
		'posts_per_page' => 1,
	));
	if ($chapter->found_posts != 0){
    	$post = $chapter->posts[0];
    	$wp_query = $chapter;
    	get_template_part( 'template-parts/content', 'chapter' );
    	wp_reset_postdata();
	}
	else{
	    get_template_part('no-chapter');
	}
	get_sidebar();
	get_footer();
	exit();
}
record_landing();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php if ( have_posts() ) { ?>
		    <div style="height:70vh;width:100%;background-color:#222222;"></div>
            <?php
            if (in_array($post->ID,get_stats_of('user_hidden',get_current_user_id()))){
                $class_of_eye = 'fa-eye-slash';
            }
            else{
                $class_of_eye = 'fa-eye';
            }
    		if (is_user_logged_in()){
    			$eye = '<a onclick="hide_book(' . $post->ID . ')" style="margin-left:10px;" class="btn-hover btn-floating"><i class="icon-hide far ' . $class_of_eye . '"></i></a>';
    		}
    		else{
    			$eye = '';
    		}
            ?>
          <div class="row" style="margin:0 0 15px 0;width:100%;">
            <div class="col s12" style="padding:0;">
              <ul style="margin:0">
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#about">About</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#summary">Summary</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#tags">Tags</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#follow">Follow</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#index">Index</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#author">Author</a>
              </ul>
            </div>
          </div>
          <div class="row">
            <div class="col s12">
              <div id="about" class="scrollspy">
                <?php
            	echo '<h2 style="margin:0" class="page-title book-title"> <a href="' .get_permalink($post->ID). '">' . get_the_title($post->ID) . '</a></h2>';
            	echo '<h4>by <a href="' .get_author_posts_url(get_post_field( 'post_author', $post->ID)). '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->ID )).'</a></h4>';
            	echo '<h4> Updated ' . get_the_time() . '</h4>';
            	echo "<h4>" . get_post_meta($post->ID,'word-count')[0]. " Words</h4>";
                ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="summary" class="scrollspy">
                <?php echo "" .$post -> post_excerpt . "<br>"; ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="tags" class="scrollspy">
                <?php get_template_part( 'template-parts/header', 'taxonomy' ); ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="follow" class="valign-wrapper scrollspy">
                <div style="display:inline-block;font-size:21px;" class="favorite-wrapper"><i onclick="favorite_this(<?php echo $post->ID; ?>)" class="<?php if (in_array(get_current_user_id(),get_stats_of('book_fav',$post->ID))){echo 'active ';} ?>btn-favorite far fa-heart"></i></div>
                <?php echo $eye; ?>
                <div style="display:inline-block;font-size:21px;margin-left:10px;" onclick="M.Modal.getInstance(document.getElementById('add_to_collection')).open();" class="btn-hover btn-floating"><i class="fas fa-list-ul"></i></div>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="index" class="scrollspy">
                <?php get_template_part( 'template-parts/content', 'index' ); ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="author" class="scrollspy">
                <?php get_template_part( 'template-parts/biography' ); ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
            </div>
          </div>
		    <span id="book-id" style="display:none;"><?php echo $post->ID; ?></span>
            <div class="progress" style="display:none;margin:0;position: fixed;bottom: 0px;right: 0px;left: 0px;"><div class="indeterminate"></div></div>
            <!-- Modal Structure -->
            <div id="add_to_collection" class="modal bottom-sheet">
            	<div class="modal-content">
            	 <?php
            	 $collections_of_book = wp_get_post_terms($post->ID,'collection',array('fields'=>'ids'));
            	 $collections = get_terms(array(
                    'meta_key' => 'author',
                    'meta_value' => get_current_user_id(),
                    'taxonomy' => 'collection',
                    'hide_empty' => false,
                ));
                ?>
                <table><tbody>
        			<tr>
        				<td>Favorites <label>(Private)</label></td>
        				<td class="right-align">
        					<div class="switch"><label>
        						<input onclick="favorite_this(<?php echo $post->ID ; ?>)" <?php if (in_array(get_current_user_id(),get_stats_of('book_fav',$post->ID))){echo 'checked';} ?> id="d-switch-favorite" type="checkbox">
        						<span class="lever"></span>
        						</label></div>
        				</td>
        			</tr>
        			<tr>
        				<td>Hidden <label>(Private)</label></td>
        				<td class="right-align">
        					<div class="switch"><label>
        						<input onclick="hide_book(<?php echo $post->ID ; ?>)" <?php if (in_array($post->ID,get_stats_of('user_hidden',get_current_user_id()))){echo 'checked';} ?> id="d-switch-hidden" type="checkbox">
        						<span class="lever"></span>
        						</label></div>
        				</td>
        			</tr>
        			<?php foreach($collections as $collection) { ?>
                        <tr>
                            <td><?php echo $collection->name . ' <label>(' . get_term_meta($collection->term_id,'public_collection',true) . ')</label>'; ?></td>
                            <td class="right-align">
                                <div class="switch"><label>
                                    <input onclick="save_collections();" <?php if (in_array($collection->term_id,$collections_of_book)){echo 'checked';} ?> id="switch-<?php echo $collection->term_id; ?>" type="checkbox">
                                    <span class="lever"></span>
                                </label></div>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($collections)) { ?>
                        <tr>
                            <td><a href="/dashboard?to=collections">Create Collection</a></td>
                            <td></td>
                        </tr>            
                    <?php } ?>
                </tbody></table>
            	</div>
            </div>
            <script>
                function save_collections(){
                    jQuery('.progress').css('display','block');
                    var switches = jQuery('input[id^=switch-]');
                    var collections = [];
                    for (i = 0; i < switches.length; i++) {
                        var collection_id = switches[i].id.replace('switch-','');
                        if (switches[i].checked == true){
                            collections.push(collection_id);
                        }
                    }
            		jQuery.ajax({
            			url: '/wp-content/themes/book-writer/php/add_to_collection.php',
            			type: 'post',
            			data: {ajax:1,collections:collections,book_id:document.getElementById('book-id').innerHTML},
            			success: function(response){
            				jQuery('.progress').css('display','none');
            			}
            		});
                }
            </script>
            <script>
                function hide_book(id){
                    jQuery('.progress').css('display','block');
            		if (jQuery('i.icon-hide').hasClass('fa-eye') == true){
            			jQuery('i.icon-hide').removeClass('fa-eye');
            			jQuery('i.icon-hide').addClass('fa-eye-slash');
            			jQuery('#d-switch-hidden').prop('checked',true);
            		}
            		else{
            			jQuery('i.icon-hide').removeClass('fa-eye-slash');
            			jQuery('i.icon-hide').addClass('fa-eye');
            			jQuery('#d-switch-hidden').prop('checked',false);
            		}
            		jQuery.ajax({
            			url: '/wp-content/themes/book-writer/php/hide_book.php',
            			type: 'post',
            			data: {ajax:1,id:id},
            			success: function(response){
            				jQuery('.progress').css('display','none');
            				if (response == 3){
            				    M.toast({html: 'Please login.'});
            				}
            				else if (response == 0){
            				    jQuery('#d-switch-hidden').prop('checked',false);
            					jQuery('i.icon-hide').removeClass('fa-eye-slash');
            					jQuery('i.icon-hide').addClass('fa-eye');
            				}
            				else if (response == 1){
            				    jQuery('#d-switch-hidden').prop('checked',true);
            					jQuery('i.icon-hide').removeClass('fa-eye');
            					jQuery('i.icon-hide').addClass('fa-eye-slash');
            				}
            			}
            		});
                }
            	function favorite_this(id){
            		jQuery('.favorite-wrapper')[0].innerHTML = '<div class="loader" style="width:21px;height:21px;"></div>';
            		jQuery.ajax({
            			url: '/wp-content/themes/book-writer/php/favorite_this.php',
            			type: 'post',
            			data: {ajax:1,book_id:id},
            			success: function(response){
            				jQuery('.favorite-wrapper')[0].innerHTML = '<i onclick="favorite_this(' + id + ')" class="btn-favorite far fa-heart"></i>';
            				if (response == 0){
            					jQuery('.btn-favorite').removeClass('active');
            					jQuery('#d-switch-favorite').prop('checked',false);
            				}
            				else if (response == 1){
            					jQuery('.btn-favorite').addClass('active');
            					jQuery('#d-switch-favorite').prop('checked',true);
            				}
            				else if (response == 3){
            					jQuery('#login-modal').modal('open');
            				}
            			}
            		});
            	}
            </script>
            <script>
              jQuery(document).ready(function(){
                jQuery('.scrollspy').scrollSpy();
              });
            </script>
		<?php } else {
			get_template_part( 'template-parts/content', 'bookempty' );
		} ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>