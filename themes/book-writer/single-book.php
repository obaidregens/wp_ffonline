<?php

global $vfs;
$vfs = vfs();
log_stats('view','book',$post->ID,$vfs);

global $js_bundle;
$js_bundle = global_bundle('book');
$js_bundle->add('book');
$js_bundle->add('chapter');
$js_bundle->enqueue();

if (current_user_can('administrator') && isset($_GET['template']) && $_GET['template'] == 'test'){
    include('single-book-test.php');
    exit();
}
/**
* The template for displaying book pages
* Template Name: Book
*
**/

?>
<?php
//page_header
get_header();
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php if ( have_posts() ) { ?>
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
          <div class="row" style="white-space:nowrap;overflow:auto;z-index:10;background-color:var(--background-accent);position:sticky;top:0;margin:0 0 15px 0;width:100%;">
            <div class="col s12" style="padding:0;">
              <ul style="margin:0">
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#about">About</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#summary">Summary</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#tags">Tags</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#follow">Follow</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#index">Index</a>
                <a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="#author">Author</a>
				<a class="btn-hover" style="display:inline-block;padding:10px 10px;" href="<?php echo get_post_meta($post->ID,'link',true); ?>" rel="nofollow" target="_blank">Source</a>
              </ul>
            </div>
          </div>
          <div class="row">
            <div class="col s12">
              <div id="about" class="scrollspy">
                <?php
            	echo '<h2 style="margin:0" class="page-title book-title"> <a href="' .get_permalink($post->ID). '">' . get_the_title($post->ID) . '</a></h2>';
            	echo '<h4>by <a href="' .get_author_posts_url(get_post_field( 'post_author', $post->ID)). '">' .get_the_author_meta('display_name',get_post_field( 'post_author', $post->ID )).'</a></h4>';
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
                            <td><a href="/dashboard/collections">Create Collection</a></td>
                            <td></td>
                        </tr>            
                    <?php } ?>
                </tbody></table>
            	</div>
            </div>
		<?php } else {
			get_template_part( 'template-parts/content', 'bookempty' );
		} ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>