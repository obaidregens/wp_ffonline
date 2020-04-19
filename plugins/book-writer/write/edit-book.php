<?php
function edit_book($book){
	?>
	<!-- New Post Form -->
	<form name="editbook" method="post" class="col s12">
		<input type="hidden" name="id" value="<?php if ($book != 'new'){echo $book->ID;}else{echo 'new';} ?>">
		<ul class="collapsible">
			<li class="active">
				<div class="collapsible-header">About</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
					        <input id="book_title" name="book_title" type="text" class="validate" data-length="40" value="<?php if ($book != 'new' ){ echo $book->post_title; }?>">
							<label for="book_title">Book Title*</label>
						</div>
					</div>
					<div class="row">
						<div class="input-field col s12">
        							<textarea id="book_description" name="book_description" class="materialize-textarea" data-length="400"><?php if ($book != 'new' ){ echo $book->post_excerpt;} ?></textarea>
							<label for="book_description">Description*</label>
						</div>
					</div>
					<div class="input-field"><span class="helper-text">If your fandom isn't listed, you can <a target="_blank" href="/fandom">create</a> it.</span></div>
					<div class="row">
						<div class="input-field col s12">
							<select multiple style="display:none;" id="category" name="category[]">
								<?php
								if ($book != 'new' ){
									$book_term_objs = wp_get_post_terms($book->ID,'category');
									$book_terms = array();
									foreach ($book_term_objs as $book_term_obj){array_push($book_terms,$book_term_obj->parent);}
								}
								else{
								    $book_terms = array();
								}
								?>
								<?php
									$terms_c = get_terms(array(
										'taxonomy' => 'category',
										'hide_empty' => false,
										'parent'    => 0,
									));
									foreach ($terms_c as $term){
										?><option <?php if (in_array($term->term_id, $book_terms)){echo 'selected';} ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option><?php
									}
								?>
							</select>
							<label>Category*</label>
						</div>
					</div>
					<div class="row">
						<div class="input-field col s12">
							<select class="validate" data-limit="3" searchable="Search Fandoms" multiple style="display:none;" id="fandom" name="fandom[]">
							<?php //options to be added with js ?>
							<?php
							    if ($book != 'new' ){
									$p_terms = wp_get_post_terms($book->ID,'category');
									$p_terms_s = array();
									foreach ($p_terms as $book_term_obj){array_push($p_terms_s,$book_term_obj->term_id);}
									$cats = array();
									foreach($p_terms as $term){
									    if (! in_array($term->parent,$cats)){
									        $cats[] = $term->parent;
									    }
									}
									foreach($cats as $cat){
									    $category = get_term($cat);
										$terms = get_terms(array(
											'taxonomy' => 'category',
											'hide_empty' => false,
											'parent'    => $cat,
										));
									    ?><optgroup label="<?php echo $category->name; ?>">
									    <?php foreach($terms as $term){ ?>
									        <option <?php if(in_array($term->term_id,$p_terms_s)){echo 'selected';} ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
									    <?php } ?>
									    </optgroup><?php
									}
							    }
							?>
							</select>
							<label>Fandom*</label>
							<span class="helper-text">Please select upto three fandoms for a crossover.</span>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Tags</div>
				<div class="collapsible-body">

					<?php $taxonomies = array('rating','language','status','genre');?>
					<?php $multi_taxonomies = array('genre');?>
					<?php $required_taxonomies = array('rating','language','status');?>
					<?php foreach ($taxonomies as $taxonomy){ ?>
						<div class="row">
							<div class="input-field col s12">
								<select searchable="Search <?php echo ucfirst($taxonomy); ?>" <?php if (in_array($taxonomy, $multi_taxonomies)){echo 'multiple';}?> style="display:none;" id="<?php echo $taxonomy; ?>" name="<?php echo $taxonomy; if (in_array($taxonomy, $multi_taxonomies)){echo '[]';} ?>">
										<?php if (in_array($taxonomy, $multi_taxonomies) == false){echo '<option value="" disabled selected>Please Select a ' . ucfirst($taxonomy) . '</option>';}?>
										<?php
											$book_term_objs = wp_get_post_terms($book->ID,$taxonomy);
											$book_terms = array();
											foreach ($book_term_objs as $book_term_obj){array_push($book_terms,$book_term_obj->slug);}
										?>
										<?php
											$terms = get_terms(array(
												'taxonomy' => $taxonomy,
												'hide_empty' => false,
											));
											foreach ($terms as $term){
												?><option <?php if (in_array($term->slug, $book_terms)){echo 'selected';} ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option><?php
											} ?>
								</select>
								<label><?php echo ucfirst($taxonomy); if (in_array($taxonomy, $required_taxonomies)){echo '*';} ?></label>
							</div>
						</div>
					<?php } ?>
					<div class="row"><div class="col s12"><label>Characters</label></div></div>
					<div class="row">
						<div class="col s12" id="character_wrapper">
						    <?php
						    $no_char_msg = '<div><label>Choose your fandom to select characters.</label></div>';
						    if ($book != 'new' ){
								$book_term_objs = wp_get_post_terms($book->ID,'character');
								if (empty($book_term_objs)){
                                    echo $no_char_msg;
								}
								$book_cats = wp_get_post_terms($book->ID,'category');
								foreach($book_cats as $cat){
									$terms = array();
									foreach ($book_term_objs as $book_term_obj){
										if ($book_term_obj->parent == $cat->term_id){
											$terms[] = $book_term_obj->name;
										}
									}
									$terms = implode(',',$terms);
									?>
									<div>
										<input value="<?php echo $terms; ?>" style="display:none;" id="character_input_<?php echo $cat->slug; ?>">
									</div>
									<?php       							                
								}
						    }
						    else{
						        echo $no_char_msg;
						    }
						    ?>
						</div>
					</div>
					<div class="row"><div class="col s12"><label>Pairings</label></div></div>
					<div id="pairing-box">
						<?php
						if ($book != 'new' ){
							$book_term_objs = wp_get_post_terms($book->ID,'pairing');
							$book_terms = array();
							foreach ($book_term_objs as $book_term_obj){array_push($book_terms,$book_term_obj->name);}
							$i = 1;
							foreach($book_terms as $book_term) { 
								$book_term = explode('/',$book_term);
								?>
								<div class='row pairing_cont'>
									<div class="input-field col s12">
										<select searchable="Search Pairing" multiple style="display:none;" name="pairing_<?php echo $i; ?>[]">
											<?php
											$terms = wp_get_post_terms($book->ID,'character');
											foreach ($terms as $term){
												?> <option <?php if (in_array($term->name,$book_term)){echo 'selected';} ?> value="<?php echo $term->name ?>"><?php echo $term->name ?></option><?php
											}
											?>													
										</select>
										<label>Pairing <?php echo $i; ?></label>
									</div>
								</div>
							<?php $i++;
							}
						} ?>
					</div>
					<div class="row" id="pairing_btn_wrapper">
						<div class="col s12">
						    <label>Select your characters.</label>
						</div>
					</div>
					<div class="row">
						<div class="col s12">
							<div class="chips" id="tags"></div>
							<input style="display:none;" name="tag" id="tags_input" value="">
							<?php
							if ($book != 'new' ){
							    $tag_objs = wp_get_post_terms($book->ID,'tag');
							}
							else{
							    $tag_objs = array();
							}
							echo '<optgroup style="display:none;" id="initial_tags">';
							foreach ($tag_objs as $tag_obj){
								echo '<option value="' . $tag_obj->name . '"></option>';
							}
							echo '</optgroup>';
							?>
							
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Extras</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
							<textarea id="detailed_description" name="detailed_description" class="materialize-textarea"><?php if ($book != 'new' ){echo $book->post_content;} ?></textarea>
							<label for="detailed_description">Detailed Description</label>
						</div>
					</div>
					<?php
					if ($book == 'new'){
						$anon_review = 'false';
					}
					else{
						$anon_review = get_post_meta($book->ID,'anon_review',true);
					}
					?>
					<div class="switch center-block" style="padding-left:10px;">
						<label>
							<label id="anon-review-label">Anonymous Reviews</label>
							<input id="anon-review" name='anon-review' type='checkbox' value='true' <?php if ($anon_review == 'true'){echo 'checked';}?>>
							<span class="lever"></span>
						</label>
					</div>
				</div>
			</li>
		</ul>
		<div class="switch center-block" style="padding-left:10px;">
			<label>
				<label id="publish-label"><?php if ($book != 'new' && $book->post_status == 'publish'){echo 'Publish';}else{echo 'Draft';}?></label>
				<?php if ($book != 'new') {$chapters = get_posts(array('post_type'=>'chapter','post_status'=>array('publish','draft'),'post_parent'=>$book->ID)); } ?>
				<input onclick="change_publish_label()" id="publish" name='publish' type='checkbox' value='publish' <?php if ($book == 'new' || empty($chapters)){echo 'disabled';}?><?php if ($book != 'new' && $book->post_status == 'publish'){echo 'checked';}?>>
				<span class="lever"></span>
			</label>
		</div>
		<div style="text-align:right;">
		<button <?php if ($book == 'new' || $book->post_status == 'draft') {echo 'disabled';} ?> class="waves-effect waves-light btn-small modal-trigger" id="chapters-trigger" data-target="select-chapters">Chapters</button>
        <div id="button-wrapper" style="display:inline-block;padding-right: 15px;padding-left: 15px;"><button id="submit-btn" class="waves-effect waves-light btn-small" type="submit">Save</button></div>
		</div>
		<div id="reCAPTCHA_div" style="margin-top:15px;" class="g-recaptcha" data-sitekey="6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth"></div>

	</form>
	<?php
    if ($book != 'new'){
        	$chapters = get_posts(array(
        	    'post_parent'   => $book->ID,
        		'post_type'		=> 'chapter',
        		'sort_column'	=> 'post_modified',
        		'post_status'	=> array('publish','draft'),
        		'sort_order'	=> 'DESC',
        		'posts_per_page'=> -1
        	));
        	?>
        	
          <!-- Modal Structure -->
          <div id="select-chapters" class="modal">
            <div class="modal-content">
                <h4 class="row">Select chapters to publish</h4>
                <?php foreach ($chapters as $chapter){ ?>
              <div class="row"><label>
                <input type="checkbox" value="<?php echo $chapter->ID; ?>" <?php if ($chapter->post_status == 'publish'){echo 'checked';} ?> />
                <span><?php echo $chapter->post_title; ?></span>
              </label></div>
              <?php } ?>
            </div>
          </div>
      <?php } ?>
    <script type = "text/javascript">
        jQuery('.modal').modal();
		jQuery("form[name='editbook']").submit(function(event) {
			event.preventDefault();
			M.Toast.dismissAll();
			var reCAPTCHA = grecaptcha.getResponse();
			var all_chapters = jQuery('#select-chapters.modal input[type=checkbox]');
			var selected_chapters = [];
        	for (i = 0; i < all_chapters.length; i++) {
        	    if (all_chapters[i].checked == true){
        	        selected_chapters.push(all_chapters[i].value);
        	    }
        	}
        	var is_there_error = false;
    		if(document.querySelector("#publish").checked == true && (document.querySelector("#book_title").value == "" || document.querySelector("#book_description").value == "" || document.querySelector("#book_description").value.length > 400 || document.querySelector("#book_title").value.length > 40 || document.querySelector("#fandom").value == "" || document.querySelector("#rating").value == "" || document.querySelector("#language").value == "" || document.querySelector("#status").value == "")) {
                M.toast({html: 'Please fill all the required fields.'});
                is_there_error = true;
    		}
    		if (document.querySelector("#publish").checked == true && selected_chapters.length == 0){
                M.toast({html: 'Select the chapters you want to publish.'});
                jQuery('#select-chapters').modal('open');
                is_there_error = true;
    		}
			if (reCAPTCHA == ''){
				M.toast({html: 'Please confirm you are not a robot by verifying yourself.'});
				is_there_error = true;
			}
			if (is_there_error == false){
				document.getElementById("button-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
				var instance = M.Chips.getInstance(jQuery('#tags'));
				var tagsData = instance.chipsData;
				var tags = [];
            	for (i = 0; i < tagsData.length; i++) {
            	    tags.push(tagsData[i].tag);
            	}
            	var charsData = jQuery('div[id^=character_].chips');
            	var characters = {};
            	for (i = 0; i < charsData.length; i++) {
            	    var instance = M.Chips.getInstance(charsData[i]);
            	    var charData = instance.chipsData;
            	    var chars = [];
                	for (j = 0; j < charData.length; j++) {
                	    chars.push(charData[j].tag);
                	}
            	    characters[charsData[i].id.replace('character_','')] = chars;
            	}
            	var pairing_elems = jQuery('select[name^=pairing]');
            	var pairings = [];
            	for (i = 0; i < pairing_elems.length; i++) {
                    pairings.push(jQuery(pairing_elems[i]).val().join('/'));
            	}
				var id = jQuery('input[name=id]').val();
				var title = jQuery('#book_title').val();
				var description = jQuery('#book_description').val();
				var detailed_desc = jQuery('#detailed_description').val();
				var category = jQuery('#fandom').val();
				var rating = jQuery('#rating').val();
				var language = jQuery('#language').val();
				var status = jQuery('#status').val();
				var genre = jQuery('#genre').val();
				if (document.querySelector("#publish").checked == true){
					var publish = 'publish';
				}
				else{
					var publish = 'draft';
				}
				if (document.querySelector("#anon-review").checked == true){
					var anon_review = 'true';
				}
				else{
					var anon_review = 'false';
				}
				jQuery.ajax({
					url: '/wp-content/themes/book-writer/dashboard/ajax/submit-book.php',
					type: 'post',
					dataType: 'JSON',
					data: {ajax: 1,reCAPTCHA:reCAPTCHA,id:id,title:title,description:description,category:category,rating:rating,language:language,status:status,genre:genre,characters:characters,pairing:pairings,tag:tags,publish:publish,detailed_desc:detailed_desc,selected_chapters:selected_chapters,anon_review:anon_review},
					success: function(return_data){
					    grecaptcha.reset();
					    M.Toast.dismissAll();
					    document.getElementById("button-wrapper").innerHTML = '<button id="submit-btn" class="waves-effect waves-light btn-small" type="submit">Save</button>';
					    response = return_data.response;
						if (response == '1'){
							M.toast({html: 'Book Published.'});
						}
						else if (response == '2'){
							M.toast({html: 'Book Saved.'});
						}
						else if (response == '3' || response == '8' || response == '4'){
							M.toast({html: 'There was a problem saving your book.'});
						}
						else{
							M.toast({html: 'An unknown error occured.'});
						}
					    if ((response == 1 || response == 2) && id == 'new'){
					        window.location.href = document.location.origin + '/dashboard/edit-book?edit-book=' + return_data.id;
					    }
					}
				});
			}
			
		});
		function change_publish_label(){
			if (document.querySelector("#publish-label").innerHTML == 'Publish'){
				document.querySelector("#publish-label").innerHTML = 'Draft';
				jQuery('#chapters-trigger').prop('disabled',true);
			}
			else{
				document.querySelector("#publish-label").innerHTML = 'Publish';
				jQuery('#chapters-trigger').prop('disabled',false);
				jQuery('#select-chapters').modal('open');
			}
		}
    </script>
    <script>
        function get_all_chars(){
            var char_of = jQuery('div[id^=character_].chips');
            var full_chars = [];
        	for (i = 0; i < char_of.length; i++) {
                var instance = M.Chips.getInstance(char_of[i]);
                full_chars = full_chars.concat(instance.chipsData);
        	}
        	return full_chars;
        }
        function add_char_chip(event, chip){
            var instance = M.Chips.getInstance(jQuery(event[0]));
            var full_chars = get_all_chars();
			if (full_chars.length > 6){
				instance.deleteChip(instance.chipsData.length-1);
				return;
			}
			jQuery('#pairing-box').children().remove();
    		if (full_chars.length > 1){
                jQuery('#pairing_btn_wrapper').children().remove();
                jQuery('#pairing_btn_wrapper').append('<div class="col s12 center"><button style="z-index:0;right:10px" type="button" class="btn-floating hoverable btn-small" onclick="addpairing()"><i class="fas fa-plus"></i></button><button style="z-index:0;left:10px" type="button" class="btn-floating hoverable btn-small" onclick="removepairing()"><i class="fas fa-minus"></i></button></div>');
    		}
        }
        function delete_char_chip(event, chip){
            var instance = M.Chips.getInstance(jQuery(event[0]));
        	var fandom_slug = event[0].id.replace('character_','');
        	jQuery('#pairing-box').children().remove();
            var full_chars = get_all_chars();
    		if (full_chars.length < 2){
        	    jQuery('#pairing_btn_wrapper').children().remove();
        	    jQuery('#pairing_btn_wrapper').append('<div class="col s12"><label>Choose your fandom to select characters.</label></div>');
    		}
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
        		jQuery('[id=tags]').chips({
        			autocompleteOptions: {
        				data: tag_opts,
        				limit: Infinity,
        				minLength: 1
        			},
        			limit: 5,
        			placeholder: 'Enter a tag',
        			secondaryPlaceholder: 'Add upto 5',
        		});
        		//Saved Tags START
    			var instance = M.Chips.getInstance(jQuery('#tags'));
    			var raw_initial = jQuery('#initial_tags').children();
    			for (var j = 0; j < raw_initial.length; j++) {
    				var opt = jQuery(raw_initial[j]).val();
    				instance.addChip({
    					tag: opt,
    				});
    			}
        		//Saved Tags END
        		//Tag Chips END (Custom Tags)
        		
        		
        		//Saved Characters START
        		if (jQuery('#fandom').val() !== null){
            		for (main_i = 0; main_i < jQuery('#fandom').val().length; main_i++) {
            		    character_opts = {};
            		    var fandom_slug = jQuery('#fandom').val()[main_i];
            		    var fandom_name = jQuery('span[label="' + fandom_slug + '"]')[0].getAttribute("name");
            			var opts = jQuery('span[label="' + fandom_slug + '"]')[0].innerHTML.split(',');
            			opts.pop();
            			jQuery("#character_input_" + fandom_slug).parent()[0].innerHTML = '<label for="character_' + fandom_slug + '">' + fandom_name + '</label><div class="chips" id="character_' + fandom_slug + '"></div><input value="' + jQuery("#character_input_" + fandom_slug).val() + '" style="display:none;" id="character_input_' + fandom_slug + '">';
            			for (j = 0; j < opts.length; j++) {
            			    character_opts[opts[j]] = null;
            		    }
                		jQuery("#character_" + fandom_slug).chips({
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
                		var values = jQuery("#character_input_" + fandom_slug).val().split(',');
                		jQuery("#character_input_" + fandom_slug).remove();
                		instance = M.Chips.getInstance(jQuery("#character_" + fandom_slug));
                		var pairings_inner = jQuery('#pairing-box').children();
                		
            			for (c = 0; c < values.length; c++) {
            				instance.addChip({
            					tag: values[c],
            				});
            			}
            			jQuery('#pairing-box').append(pairings_inner);
            			
            			//Put characters in chips END
            		}
        		}
        		//Saved Characters END
        		jQuery('.collapsible').collapsible();
        		jQuery('.tooltipped').tooltip();
                jQuery('textarea[data-length],input[data-length][type="text"]').characterCounter();
        		jQuery('select').formSelect();
        		          ////////////////----------------                   ----------------/////////////////////
        		/////////////////////---------------------Non-Ready functions---------------------/////////////////////
        		          ////////////////----------------                   ----------------/////////////////////
        		
        		jQuery('[id=category]').on('change', function() {
        		    
        			jQuery('#pairing-box').children().remove();
        			jQuery(this).data('value', jQuery(this).val());
        			
        			//Add Fandom
                    jQuery('#fandom').children().remove();
                    if (jQuery(this).val() !== null){
                    	for (i = 0; i < jQuery(this).val().length; i++) {
                    		opts = jQuery('optgroup[label="f-' + jQuery(this).val()[i] + '"]')[0];
                    		label = jQuery('option[value="' + jQuery(this).val()[i] + '"]')[0];
                    		label = label.innerHTML;
                    		jQuery('#fandom').append('<optgroup label="' + label + '">' + opts.innerHTML + '</optgroup>');
                    	}
                    }
                    jQuery('#fandom').formSelect();
        			// Till here
        		});
        		jQuery('[id=fandom]').on('change', function() {
        			if (jQuery(this).val() === null){
        			    jQuery("#character_wrapper").children().remove();
        			    jQuery("#character_wrapper").append('<div><label>Choose your fandom to select characters.</label></div>');
        			}
        			else if (jQuery(this).val().length > jQuery(this).data('limit')) {
        				jQuery(this).val(jQuery(this).data('value'));
        				jQuery('select').formSelect();
        			}
        			else {
        				jQuery(this).data('value', jQuery(this).val());
        				jQuery("#character_wrapper").children().remove();
        				// Added
                		for (i = 0; i < jQuery('#fandom').val().length; i++) {
                		    
                		    //Restart Pairing
                		    jQuery('#pairing-box').children().remove();
                		    
                		    character_opts = {};
                		    var fandom_slug = jQuery('#fandom').val()[i];
                		    var fandom_name = jQuery('span[label="' + fandom_slug + '"]')[0].getAttribute("name");
                			var opts = jQuery('span[label="' + fandom_slug + '"]')[0].innerHTML.split(',');
                			opts.pop();
                			jQuery("#character_wrapper").append('<div><label for="character_' + fandom_slug + '">' + fandom_name + '</label><div class="chips" id="character_' + fandom_slug + '"></div></div>');
                			for (j = 0; j < opts.length; j++) {
                			    character_opts[opts[j]] = null;
                		    }
                    		jQuery("#character_" + fandom_slug).chips({
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
<?php
	$terms_top_cat = get_terms(array(
		'taxonomy' => 'category',
		'parent'   => 0,
		'hide_empty' => false,
	));
	foreach($terms_top_cat as $term_c){
		echo '<optgroup style="display:none;" label="f-' . $term_c->slug . '">';
		$terms = get_terms(array(
			'taxonomy' => 'category',
			'hide_empty' => false,
			'parent' => $term_c-> term_id,
		));
		foreach ($terms as $term){
			echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
		}
		echo '</optgroup>';
	}
?>
<?php
	$terms_c = get_terms(array(
		'taxonomy' => 'category',
		'hide_empty' => false,
		'childless'=> true,
	));
	foreach($terms_c as $term_c){
		echo '<span style="display:none;" name="' . $term_c->name . '" label="' . $term_c->slug . '">';
		$terms = get_terms(array(
			'taxonomy' => 'character',
			'hide_empty' => false,
			'parent' => $term_c-> term_id,
		));
		foreach ($terms as $term){
			echo $term->name . ',';
		}
		echo '</span>';
	}
?>
<?php
	echo '<optgroup style="display:none;" id="get_tag_opts">';
	$terms = get_terms(array(
		'taxonomy' => 'tag',
		'hide_empty' => false,
	));
	foreach ($terms as $term){
		echo '<option value="' . $term->name . '"></option>';
	}
	echo '</optgroup>';
?>
<script type = "text/javascript">
	function addpairing(){
		var i = jQuery("#pairing-box").children().length;
        var char_objs = get_all_chars();
        var chars = [];
		for (j = 0; j < char_objs.length; j++) {
		    chars.push(char_objs[j].tag);
	    }

		var options = '';
		for (j = 0; j < chars.length; j++) {
		    options += '<option value="' + chars[j] +'">' + chars[j] +'</option>';
	    }
		if (i < 3) {
			jQuery("#pairing-box").append('<div class="row pairing_cont"><div class="input-field col s12"><select searchable="Search Pairing" multiple style="display:none;" name="pairing_' + (i+1) + '' + '[]">' + options + '</select><label>Pairing ' + (i+1) + '</label></div></div>').find("select").formSelect();
		}
	};
	function removepairing(){
		
		var i = jQuery("#pairing-box").children().length;
		if (i > 0){
			jQuery("select[name=pairing_" + i + "" + "\\[\\]]").parents('.pairing_cont').remove();
		}
	};
</script>
    <?php
}