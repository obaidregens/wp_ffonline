<?php

global $vfs;
$vfs = vfs();
log_stats('view','book',$post->ID,$vfs);

global $js_bundle;
$js_bundle = global_bundle('book');
$js_bundle->add('book');
$js_bundle->add('book-options');
$js_bundle->enqueue();

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
<style>
.book-bar{
  margin:0;
  text-transform: uppercase;
  font-weight:bold;
}
.book-bar > a {
  display:inline-block;
  padding:10px 10px;
  cursor: pointer;
}
.book-bar > a > i {
  margin-right: 10px;
}
.book-bar > a:hover > i {
  color: var(--secondary-color);
}
</style>
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
              <ul class="book-bar">
                <a class="btn-hover" href="#about">About</a>
                <a class="btn-hover" href="#summary">Summary</a>
                <a class="btn-hover" href="#tags">Tags</a>
                <a class="btn-hover" href="#index">Index</a>
                <a class="btn-hover" href="#author">Author</a>
                <?php if (metadata_exists('post',$post->ID,'link')){ ?>
                <a class="btn-hover" href="<?php echo get_post_meta($post->ID,'link',true); ?>" rel="nofollow" target="_blank">Source</a>
                <?php } ?>
                <a class="btn-hover right book-collections" book_id="<?= $post->ID ?>"><i class="fas fa-plus"></i>Add to collection</a>
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
              <div id="index" class="scrollspy">
                <?php get_template_part( 'template-parts/content', 'index' ); ?>
              </div>
              <div id="author" class="scrollspy">
                <?php get_template_part( 'template-parts/biography' ); ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
            </div>
          </div>
          <?php get_template_part('template-parts/modal','collection'); ?>
		<?php } else {
			get_template_part( 'template-parts/content', 'bookempty' );
		} ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>