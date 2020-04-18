<?php
	$books = get_pages(array(
		'authors'		=> get_current_user_id(),
		'post_type'		=> 'book',
		'sort_column'	=> 'post_modified',
		'post_status'	=> array('publish','draft'),
		'sort_order'	=> 'DESC'
	)); ?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<ul class="collapsible">
				<?php foreach($books as $book){?>
					<li>
						<div class="collapsible-header" style="display:block;"><?php if (preg_replace('/\s+/', '',$book->post_title) == ''){echo "(No Book Title)";}else{echo $book->post_title;}?>
							<label style="padding-left:10px;"><a class="modal-trigger" data-target="<?php echo $book->ID; ?>">Edit</a><?php if ($book->post_status == 'publish'){?> / <a href="<?php echo get_permalink($book->ID); ?>">View</a><?php } ?></label>
							<label class="right"><?php if ($book->post_status == 'publish'){echo 'Published';}else if ($book->post_status == 'draft'){echo 'Draft';} ?></label>
						</div>
						<div class="collapsible-body">
							<?php if (count(get_posts(array('post_type'=>'chapter','post_status'=>array('publish','future'),'post_parent'=>$book->ID,'posts_per_page'=>-1))) > 1){ ?>
								<button class="waves-effect waves-light btn-small modal-trigger" style="width:100%" data-target="order-<?php echo $book->ID; ?>">Order Chapters</button>
							<?php }
							$chapters = get_posts(array(
								'authors'		=> get_current_user_id(),
								'post_type'		=> 'chapter',
								'post_status'	=> array('publish','draft','future'),
								'post_parent'	=> $book->ID,
								'posts_per_page'=> -1
							)); ?>
							<ul class="collapsible">
								<?php foreach($chapters as $chapter) { ?>
									<li>
										<div class="collapsible-header" style="display:block;"><?php if (preg_replace('/\s+/', '',$chapter->post_title) == ''){echo "(No Chapter Title)";}else{echo $chapter->post_title;}?>
											<label style="padding-left:10px;"><a onclick="load_page('edit-chapter','<?php echo $chapter->ID; ?>')" >Edit</a></label>
											<label class="right"><?php if ($chapter->post_status == 'publish'){echo 'Published';}else if ($chapter->post_status == 'draft'){echo 'Draft';}else{echo 'Scheduled';}?></label>
										</div>
									</li>
								<?php } ?>
								<li>
									<div class="collapsible-header"><i class="large material-icons">add</i><a>New Chapter</a></div>
									<div class="collapsible-body">
										<?php new_chapter($book);?>
									</div>
								</li>
							</ul>
						</div>
					</li>
				<?php } ?>
				<li>
					<div class="collapsible-header modal-trigger" data-target="new"><i class="large material-icons">add</i><a>New Book</a></div>
				</li>
			</ul>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
	
	<?php edit_book_modals($books); ?>
	<?php order_book_modals($books); ?>
	<?php get_template_part('write/book','new'); ?>
<script>
    function add_char_chip(event, chip){
        var identifier = event[0].id.replace('character_','');
        if (event[0].id.includes('-new')){
            var id = 'new';
        }
        else{
            var id = event[0].id.match(/\d+/)[0];
        }
        jQuery('#pairing-box_' + id).children().remove();
        jQuery('#pairing_btn_wrapper-' + id).children().remove();
        jQuery('#pairing_btn_wrapper-' + id).append('<div class="col s12 center"><button style="z-index:0;right:10px" type="button" class="btn-floating hoverable btn-small" onclick="addpairing(\'' + id + '\')"><i class="fas fa-plus"></i></button><button style="z-index:0;left:10px" type="button" class="btn-floating hoverable btn-small" onclick="removepairing(\'' + id + '\')"><i class="fas fa-minus"></i></button></div>');
        //Chips Data START
        //Pretty self explanatory, but what we're doing is putting the value of the chip into a hidden input tag
        var chip_val = chip.innerHTML.replace('<i class="material-icons close">close</i>','');
    	var tags = jQuery("#character_input_" + identifier).attr('value');
    	if (tags === '' || tags == 'undefined'){
    	    tags = [];
    	}
    	else{
    	    tags = tags.split(',');
    	}
    	tags.push(chip_val);
    	jQuery("#character_input_" + identifier).attr('value',tags);
    	//Chips Data END    
    }
    function delete_char_chip(event, chip){
        //Chips Data START
        var chip_val = chip.innerHTML.replace('<i class="material-icons close">close</i>','');
    	
    	var identifier = event[0].id.replace('character_','');
        if (event[0].id.includes('-new')){
            var id = 'new';
        }
        else{
            var id = event[0].id.match(/\d+/)[0];
        }
    	jQuery('#pairing-box_' + id).children().remove();
    	var tags = jQuery("#character_input_" + identifier).attr('value');
    	if (tags.includes(",") === false){
    	    jQuery("#character_input_" + identifier).attr('value','');
    		var raw_datas = jQuery('[id ^=character_input_][id $=-' + id + ']');
    		data = [];
    		for (j = 0; j < raw_datas.length; j++) {
    		    if(raw_datas[j].value != ''){
    		        data = data.concat(raw_datas[j].value.split(','));
    		    }
    	    }
    	    if (data.length == 0){
        	    jQuery('#pairing_btn_wrapper-' + id).children().remove();
        	    jQuery('#pairing_btn_wrapper-' + id).append('<div class="col s12"><label>Choose your fandom to select characters.</label></div>');
    	    }
    	}
    	else{
    	    tags = tags.split(',');
    	    tags.indexOf(chip_val);
    	    tags.splice(tags.indexOf(chip_val), 1);
    	    jQuery("#character_input_" + identifier).attr('value',tags);
    	    
    	}
    	//Chips Data END    
    }
    jQuery(document).ready
    (
    	function()
    	{
    	    //Tag Chips START (Custom Tags)
    		var tag_opts = {};
    		var tag_opts_val = [];
    		
    		var raw_tag_opts = jQuery('#get_tag_opts').children();
    		for (i = 0; i < raw_tag_opts.length; i++) {
    			tag_opts_val.push(jQuery(raw_tag_opts[i]).val());
    		}
    
    		for (i = 0; i < tag_opts_val.length; i++) {
    			tag_opts[tag_opts_val[i]] = null;
    		}
    		jQuery('[id^=tags-]').chips({
    			autocompleteOptions: {
    				data: tag_opts,
    				limit: Infinity,
    				minLength: 1
    			},
    			limit: 5,
    			placeholder: 'Enter a tag',
    			secondaryPlaceholder: 'Add upto 5',
    			onChipAdd: (event, chip) => {
    			    //Chips Data START
    			    //Pretty self explanatory, but what we're doing is putting the value of the chip into a hidden input tag
    			    var chip_val = chip.innerHTML.replace('<i class="material-icons close">close</i>','');
    				var id = event[0].id.replace('tags-','');
    				var tags = jQuery('#tags_input-' + id).attr('value');
    				
    				if (tags === ''){
    				    tags = [];
    				}
    				else{
    				    tags = tags.split(',');
    				}
    				tags.push(chip_val);
    				jQuery('#tags_input-' + id).attr('value',tags);
    				//Chips Data END
    			},
    			onChipDelete: (event, chip) => {
    			    //Chips Data START
    			    var chip_val = chip.innerHTML.replace('<i class="material-icons close">close</i>','');
    				var id = event[0].id.replace('tags-','');
    				var tags = jQuery('#tags_input-' + id).attr('value');
    				if (tags.includes(",") === false){
    				    jQuery('#tags_input-' + id).attr('value','');
    				}
    				else{
    				    tags = tags.split(',');
    				    tags.indexOf(chip_val);
    				    tags.splice(tags.indexOf(chip_val), 1);
    				    jQuery('#tags_input-' + id).attr('value',tags);
    				    
    				}
    				//Chips Data END
    			},
    		});
    		//Saved Tags START
    		var modals = jQuery('.edit_book_modals');
    		var book_ids = [];
    		for (var i = 0; i < modals.length; i++) {
    			var val = modals[i].id;
    			book_ids.push(val);
    		}
    		for (var p = 0; p < book_ids.length; p++) {
    			var instance = M.Chips.getInstance(jQuery('#tags-' + book_ids[p]));
    			var raw_initial = jQuery('#initial_tags-' + book_ids[p]).children();
    						
    			for (var j = 0; j < raw_initial.length; j++) {
    				
    				var opt = jQuery(raw_initial[j]).val();
    				instance.addChip({
    					tag: opt,
    				});
    			}
    		}
    		//Saved Tags END
    		//Tag Chips END (Custom Tags)
    		
    		
    		//Saved Characters START
    		var elems = jQuery('[id^=fandom-]');
    		var ids = [];
    		for (i = 0; i < elems.length; i++) {
    		    if (elems[i].value !== ''){
    		        ids.push(elems[i].id.replace('fandom-',''));
    		    }
    		}
    		for(b = 0; b < ids.length; b++){
    		    var id = ids[b];
    		    
        		for (i = 0; i < jQuery('#fandom-' + id).val().length; i++) {
        		    character_opts = {};
        		    var fandom_slug = jQuery('#fandom-' + id).val()[i];
        		    var fandom_name = jQuery('span[label="' + fandom_slug + '"]')[0].getAttribute("name");
        		    var identifier = fandom_slug + "-" + id;
        			var opts = jQuery('span[label="' + fandom_slug + '"]')[0].innerHTML.split(',');
        			opts.pop();
        			jQuery("#character_input_" + identifier).parent()[0].innerHTML = '<label for="character_' + identifier + '">' + fandom_name + '</label><div class="chips" id="character_' + identifier + '"></div><input value="' + jQuery("#character_input_" + identifier).val() + '" style="display:none;" name="character_' + fandom_slug + '" id="character_input_' + identifier + '">';
        			for (j = 0; j < opts.length; j++) {
        			    character_opts[opts[j]] = null;
        		    }
            		jQuery("#character_" + identifier).chips({
            			autocompleteOptions: {
            				data: character_opts,
            				limit: Infinity,
            				minLength: 1
            			},
            			placeholder: 'Enter',
            			onChipAdd: (event, chip) => {
                            add_char_chip(event, chip);
            			},
            			onChipDelete: (event, chip) => {
                            delete_char_chip(event, chip);
            			},
            		});
            		//Put characters in chips START
            		var values = jQuery("#character_input_" + identifier).val().split(',');
            		jQuery("#character_input_" + identifier).val('');
            		instance = M.Chips.getInstance(jQuery("#character_" + identifier));
            		var pairings_inner = jQuery('#pairing-box_' + id).children();
        			for (c = 0; c < values.length; c++) {
        				instance.addChip({
        					tag: values[c],
        				});
        			}
        			jQuery('#pairing-box_' + id).append(pairings_inner);
        			
        			//Put characters in chips END
        		}
    		}
    		//Saved Characters END
    		jQuery('.collapsible').collapsible();
    		jQuery('.modal').modal();
    		jQuery('.tooltipped').tooltip();
            jQuery('textarea[id^=book_description]').characterCounter();
    		jQuery('select').formSelect();
    		          ////////////////----------------                   ----------------/////////////////////
    		/////////////////////---------------------Non-Ready functions---------------------/////////////////////
    		          ////////////////----------------                   ----------------/////////////////////
    		
    		jQuery('[id^=category-]').on('change', function() {
    		    
    			var id = jQuery(this).attr('id').replace("category-", "");
    			jQuery('#pairing-box_' + id).children().remove();
    			jQuery(this).data('value', jQuery(this).val());
    			
    			//Add Fandom
                jQuery('#fandom-' + id).children().remove();
                if (jQuery(this).val() !== null){
                	for (i = 0; i < jQuery(this).val().length; i++) {
                		opts = jQuery('optgroup[label="f-' + jQuery(this).val()[i] + '"]')[0];
                		label = jQuery('option[value="' + jQuery(this).val()[i] + '"]')[0];
                		label = label.innerHTML;
                		jQuery('#fandom-' + id).append('<optgroup label="' + label + '">' + opts.innerHTML + '</optgroup>');
                	}
                }
                jQuery('#fandom-' + id).formSelect();
    			// Till here
    		});
    		jQuery('[id^=fandom-]').on('change', function() {
    			var id = jQuery(this).attr('id').replace("fandom-", "");
    			if (jQuery(this).val() === null){
    			    jQuery("#character_wrapper-" + id).children().remove();
    			    jQuery("#character_wrapper-" + id).append('<div><label>Choose your fandom to select characters.</label><input style="display:none;" name="character" id="character_input-' + id + '" value=""></div>');
    			}
    			else if (jQuery(this).val().length > jQuery(this).data('limit')) {
    				jQuery(this).val(jQuery(this).data('value'));
    				jQuery('select').formSelect();
    			}
    			else {
    				jQuery(this).data('value', jQuery(this).val());
    				jQuery("#character_wrapper-" + id).children().remove();
    				// Added
            		for (i = 0; i < jQuery('#fandom-' + id).val().length; i++) {
            		    
            		    //Restart Pairing
            		    jQuery('#pairing-box_' + id).children().remove();
            		    
            		    character_opts = {};
            		    var fandom_slug = jQuery('#fandom-' + id).val()[i];
            		    var fandom_name = jQuery('span[label="' + fandom_slug + '"]')[0].getAttribute("name");
            		    var identifier = fandom_slug + "-" + id;
            			var opts = jQuery('span[label="' + fandom_slug + '"]')[0].innerHTML.split(',');
            			opts.pop();
            			jQuery("#character_wrapper-" + id).append('<div><label for="character_' + identifier + '">' + fandom_name + '</label><div class="chips" id="character_' + identifier + '"></div><input value="' + jQuery("#character_input_" + identifier).val() + '" style="display:none;" name="character_' + fandom_slug + '" id="character_input_' + identifier + '"></div>');
            			for (j = 0; j < opts.length; j++) {
            			    character_opts[opts[j]] = null;
            		    }
                		jQuery("#character_" + identifier).chips({
                			autocompleteOptions: {
                				data: character_opts,
                				limit: Infinity,
                				minLength: 1
                			},
                			placeholder: 'Enter',
                			onChipAdd: (event, chip) => {
                			    add_char_chip(event, chip);
                			},
                			onChipDelete: (event, chip) => {
                                delete_char_chip(event, chip);
                			},
                		});
            		}
    			}
    		});
        }
    )    
</script>
