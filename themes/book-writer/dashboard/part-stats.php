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
						<div class="section" style="color:var(--mid-theme-color);"><h3><?php echo $book->post_title;?><label style="padding-left:3px;"><?php if($book->post_status == 'publish'){echo ' (Published)';}else{echo ' (Draft)';} ?></label></h3></div>
						<ul class="tabs">
							<li class="tab col s4"><a href="#lastweek-<?php echo $book->ID; ?>">Last 7 Days</a></li>
							<li class="tab col s4"><a href="#alltime-<?php echo $book->ID; ?>">All Time</a></li>
							<li class="tab col s4"><a href="#performance-<?php echo $book->ID; ?>">Performance</a></li>
						</ul>
                        <div class="row" id="alltime-<?php echo $book->ID; ?>">
							<div class="section"></div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Favorites</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo count(get_stats_of('book_fav',$book->ID)); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Comments</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo count(get_stats_of('book_comments',$book->ID)); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Click Rate</div>
                                <div class="countup center-align" style="font-size: 36px;" count-append="%" data-count="<?php echo get_click_rate($book->ID); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Views</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo count(get_stats_of('views',$book->ID)); ?>">0</div>
                            </div>
                        </div>
                        <div class="row" id="lastweek-<?php echo $book->ID; ?>">
							<div class="section"></div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Favorites</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo count(get_stats_of('book_fav',$book->ID,'last week')); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Comments</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo count(get_stats_of('book_comments',$book->ID,'last week')); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Click Rate</div>
                                <div class="countup center-align" style="font-size: 36px;" count-append="%" data-count="<?php echo get_click_rate($book->ID,'last week'); ?>">0</div>
                            </div>
                            <div class="col s6 m3 book-stat">
                                <div class="center-align">Views</div>
                                <div class="countup center-align" style="font-size: 36px;" data-count="<?php echo count(get_stats_of('views',$book->ID,'last week')); ?>">0</div>
                            </div>
                        </div>
                        <div class="row" id="performance-<?php echo $book->ID; ?>">
							<div class="section"></div>
							<div class="center-align">Coming Soon!</div>
							<!--<canvas id="myChart-<?php //echo $book->ID; ?>"></canvas>-->
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
			<!--<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>-->
            <script>
				/*
				var ctx_elems = jQuery('[id^=myChart]');
				for (b = 0; b < ctx_elems.length; b++) {
					var ctx = ctx_elems[b].getContext('2d');
					var chart = new Chart(ctx, {
						// The type of chart we want to create
						type: 'line',

						// The data for our dataset
						data: {
							labels: [1, 2, 3, 4, 5, 6, 7],
							datasets: [
								{
									label: 'Favorites',
									backgroundColor: 'rgba(255, 255, 255, 0)',
									borderColor: '#ffcccb',
									data: [0, 22, 90, 44, 22, 100, 47]
								},
								{
									label: 'Comments',
									backgroundColor: 'rgba(255, 255, 255, 0)',
									borderColor: '#90ee90',
									data: [0, 3, 69, 32, 41, 88, 25, 0, 3, 69, 32, 41, 88, 25]
								},
								{
									label: 'Impressions',
									backgroundColor: 'rgba(255, 255, 255, 0)',
									borderColor: '#ffffa1',
									data: [0, 55, 103, 180, 201, 356, 491]
								},
								{
									label: 'Views',
									backgroundColor: 'rgba(255, 255, 255, 0)',
									borderColor: '#add8e6',
									data: [0, 16, 26, 41, 64, 72, 91]
								},
							]
						},

						// Configuration options go here
						options: {
							scales: {
								yAxes: [{
									ticks: {
										precision: 100
									}
								}],
							}
						},
					});
				}
				*/
				jQuery(document).ready(function(){
					jQuery('.tabs').tabs();
				});
				jQuery('.countup').each(function() {
				  var $this = jQuery(this),
					  countTo = $this.attr('data-count');
					  var append = $this.attr('count-append');
					  if (typeof append !== typeof undefined && append !== false){}
					  else{
					      append = '';
					  }
                  
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
					  $this.text(this.countNum + append);
					  //alert('finished');
					}

				  });
				});
            </script>
            <?php
        } ?>
	</main><!-- .site-main -->
</div><!-- .content-area -->