<?php

global $bundle;
$bundle = global_bundle('books');
$bundle->mix('switch');
$bundle->mix('search-options');
$bundle->js('js/book');
$bundle->enqueue();

get_header();
?>
<style>
  .book-bar{
    margin:0;
    text-transform: uppercase;
    font-weight:bold;
    display: flex;
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
  .book-bar > .book-collections{
    margin-left:auto;
  }
</style>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php if ( have_posts() ) { ?>
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
                <a class="btn-hover right book-share" book_id="<?= $post->ID ?>"><i class="fas fa-share-square"></i>Share</a>
              </ul>
            </div>
          </div>
          <div class="row">
            <div class="col s12">
              <div id="about" class="scrollspy">
                <?php
            	echo '<h2 style="margin:0" class="page-title book-title"> <a href="' .get_permalink($post->ID). '">' . get_the_title($post->ID) . '</a></h2>';
            	echo '<h4 class="book-author" >by ' . author_href($post->ID) . '</h4>';
                ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="summary" class="scrollspy">
                <?php echo "" .$post -> post_excerpt . "<br>"; ?>
              </div>
              <div class="divider" style="margin:10px 0;"></div>
              <div id="tags" class="scrollspy">
                <?php get_template_part( 'template-parts/book', 'taxonomy' ); ?>
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
          <?php
          ?>
          <collections_data hidden><?= json_encode(collection::query(array(
            'authors'  => array(get_current_user_id()),
          ))); ?></collections_data>
          <book_collections hidden><?= json_encode( collection::query_by_book( array($post->ID), 'ID' ) ); ?></book_collections>
		<?php } else {
			get_template_part( 'template-parts/content', 'bookempty' );
		} ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>