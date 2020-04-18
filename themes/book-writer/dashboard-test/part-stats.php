<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
        <?php $books = get_pages(array(
    		'authors'		=> get_current_user_id(),
    		'post_type'		=> 'book',
    		'sort_column'	=> 'post_modified',
    		'post_status'	=> array('publish','draft'),
    		'sort_order'	=> 'DESC'
    	)); ?>
        <?php foreach ($books as $book){ ?>
            <div class="row">
                <div class="col s12">
                    <div class="card-panel">
                        <div class="section" style="color:var(--mid-theme-color);"><h3><?php echo $book->post_title; ?></h3></div>
                        <div class="row">
                            
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Favorites</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo intval(get_favorites_count($book->ID)); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Comments</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo intval(get_comments_number($book->ID)); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Impressions</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo intval(get_post_meta($book->ID,'book_impressions',true)); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Views</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo intval(get_post_meta($post->ID,'book_views',true)); ?>">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if (empty($books)){
            ?>
            <script>
                load_page('dashboard');
            </script>
            <?php
        } else {
            ?>
            <script>
            jQuery('.countup').each(function() {
              var $this = jQuery(this),
                  countTo = $this.attr('data-count');
              
              jQuery({ countNum: $this.text()}).animate({
                countNum: countTo
              },
            
              {
            
                duration: 1000,
                easing:'linear',
                step: function() {
                  $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                  $this.text(this.countNum);
                  //alert('finished');
                }
            
              });
            });
            </script>
            <?php
        } ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->