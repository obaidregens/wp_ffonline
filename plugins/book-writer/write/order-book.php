<?php
function order_book_modals($books){
	foreach($books as $book){
	    if (count(get_posts(array('post_type'=>'chapter','post_status'=>array('publish','future'),'post_parent'=>$book->ID))) > 1){
    		?>
    		<!-- Modal Structure -->
    		<div id="order-<?php echo $book->ID;?>" class="modal">
    			<form name="orderbook" method="post" class="col s12" action="/wp-content/themes/book-writer/write/php/book-order.php">
    				<input type="hidden" name="bookid" value="<?php echo $book->ID;?>">
					<input type="hidden" name="author" value="<?php echo get_current_user_id();?>">
    				<div class="modal-content row" style="margin-left:0;margin-right:0;">
    				    <?php $chapters = get_posts(array(
							'authors'		=> get_current_user_id(),
							'post_type'		=> 'chapter',
							'meta_key'		=> 'chapter_order',
							'orderby'		=> 'meta_value_num',
							'order'			=> 'ASC',
							'post_status'	=> array('publish','future'),
							'post_parent'	=> $book->ID
						)); ?>
		                <div class="col s6">
    						<?php $count = 0;
    						foreach($chapters as $chapter) {
    							$count = $count + 1; ?>
                                <p>
                                  <label>
                                    <input style="height:auto;" name="chapters-order" type="radio" id="<?php echo $book->ID; ?>-chapter-num-count-<?php echo $count; ?>"/>
                                    <span id="<?php echo $book->ID; ?>-chapter-num-label-<?php echo $count; ?>" for="<?php echo $book->ID; ?>-chapter-num-<?php echo $count; ?>"><?php if (preg_replace('/\s+/', '',$chapter->post_title) == ''){echo "(No Chapter Title)";}else{echo $chapter->post_title;}?></span>
                                  </label>
                                </p>
								<input type="hidden" id="<?php echo $book->ID; ?>-chapter-num-<?php echo $count; ?>" name="<?php echo $count; ?>" value="<?php echo $chapter->ID;?>">
    						<?php } ?>
						</div>
						<div style="float:right;">
    					    <a onclick="up(<?php echo $book->ID; ?>)" style="display:block;margin-bottom: 10px;" class="btn-floating waves-effect waves-light transparent btn-large" style="box-shadow: none;-webkit-box-shadow:none;"><i class="fas fa-angle-up red fa-sm"></i></a>
                            <a onclick="down(<?php echo $book->ID; ?>)" style="display:block;" class="btn-floating waves-effect waves-light transparent btn-large" style="box-shadow: none;-webkit-box-shadow:none;"><i class="fas fa-angle-down red fa-sm"></i></a>
                        </div>
    				</div>
					
    				<div class="modal-footer">
    					<button class="waves-effect waves-light btn-small" style="margin-right: 10px;" type="submit" href="#errorcard">Save</button>
    				</div>
    			</form>

    		</div>
		<?php } ?>
	<?php } ?>
    <script type='text/javascript'>
    function up (id) {
		var num_raw = jQuery('input[name=chapters-order]:checked').attr('id');
		var num = parseInt(num_raw.replace(id + '-chapter-num-count-',''));
    	var value_bubble = jQuery('#' + id + '-chapter-num-' + (num-1)).attr('value');
		var html_bubble = document.getElementById(id + '-chapter-num-label-' + (num-1)).innerHTML;
    	jQuery('#' + id + '-chapter-num-' + (num-1)).attr('value',jQuery('#' + id + '-chapter-num-' + (num)).attr('value'));
		document.getElementById(id + '-chapter-num-label-' + (num-1)).innerHTML = document.getElementById(id + '-chapter-num-label-' + (num)).innerHTML;
        jQuery('#' + id + '-chapter-num-' + (num)).attr('value',value_bubble);
		document.getElementById(id + '-chapter-num-label-' + (num)).innerHTML = html_bubble;
    }
    function down (id) {
		var num_raw = jQuery('input[name=chapters-order]:checked').attr('id');
		var num = parseInt(num_raw.replace(id + '-chapter-num-count-',''));
    	var value_bubble = jQuery('#' + id + '-chapter-num-' + (num+1)).attr('value');
		var html_bubble = document.getElementById(id + '-chapter-num-label-' + (num+1)).innerHTML;
    	jQuery('#' + id + '-chapter-num-' + (num+1)).attr('value',jQuery('#' + id + '-chapter-num-' + (num)).attr('value'));
		document.getElementById(id + '-chapter-num-label-' + (num+1)).innerHTML = document.getElementById(id + '-chapter-num-label-' + (num)).innerHTML;
        jQuery('#' + id + '-chapter-num-' + (num)).attr('value',value_bubble);
		document.getElementById(id + '-chapter-num-label-' + (num)).innerHTML = html_bubble;
    }
    </script>
<?php }