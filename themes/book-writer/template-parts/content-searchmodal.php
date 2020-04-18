<style>
    .included_chips .chip{
        background-color:#f0f8ff;
    }
    .excluded_chips .chip{
        background-color:#fcd7d7;
    }
    .all_label div {
        font-weight: 400;
        padding-bottom: 10px;
        padding-left: 8px;
        border-bottom: 1px solid #9e9e9e;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",font-family;
    }
    [type=checkbox]:checked+span:not(.lever):before{
        transition:none!important;
    }
</style>
<div id="search-settings" style="display:none;position:absolute;top:0;left:0;width:100%;background-color:#fafafa;z-index:1000">
    <div id="custom_overlay" onclick="close_custom_modal();" style="display:none;z-index: 1001;opacity: 0.5;position: fixed;width: 100%;height: 100%;left: 0;top: 0;background-color: black;"></div>
    <a style="position:fixed;top:0;right:0;" onclick="search_settings('close');" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-times"></i></a>
	<form name="search" class="col s12">
		<div style="padding:24px;">
			<div class="row">
				<div class="input-field col s12">
					<input name="search" id="search" type="text">
					<label for="search">Search</label>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<label>Sort</label>
					<select class="browser-default" id="sort" name="sort">
						<option value="modified/DESC">Last Updated</option>
						<option value="date/DESC">Book Published</option>
						<option value="favorites/DESC">Favorites</option>
						<option value="words/DESC">Words</option>
					</select>
				</div>
			</div>
			<?php $taxonomies = array('fandom','rating','language','status','genre','character','pairing','tag');?>
			<?php $tax_labels = array('Fandom','Rating','Language','Status','Genre','Characters','Pairings','Tags');?>
			<?php $multi_taxonomies = array('fandom','genre','character','pairing','tag');?>
			<?php foreach ($taxonomies as $key => $taxonomy){ ?>
                <?php
                $get_in = explode(',',$_GET[$taxonomy . '_included']);
                $get_ex = explode(',',$_GET[$taxonomy . '_excluded']);
                $label = $tax_labels[$key];
                if ($taxonomy != 'fandom'){
    				$terms = get_terms(array(
    					'taxonomy' => $taxonomy,
    				));
                }
                else{
    				$terms = get_terms(array(
    					'taxonomy' => 'category',
    					'childless'=> true,
    				));
                }
                ?>
                <?php if (in_array($taxonomy,$multi_taxonomies)) { ?>
    				<div class="row" onclick="trigger_custom_modal('<?php echo $taxonomy; ?>-select')" id="<?php echo $taxonomy; ?>_show">
    					<div class="col s12">
    						<label><?php echo $label; ?></label>
    						<div class="chips_s included_chips" style="margin-bottom:3px;">
                                
                            </div>
    						<div class="chips_s excluded_chips">
    						    
    						</div>
    						<div class="all_label"><div class="grey-text">All</div></div>
    					</div>
    				</div>
                  <div class="custom-modal" id="<?php echo $taxonomy; ?>-select" style="background-color:#fafafa;z-index:1002;height:80%;width:85%;overflow:auto;position:fixed;top:10%;left:7.5%;display:none">
                    <div class="modal-header" style="padding:15px 20px 0px 20px;position: sticky;top: 0;left:0;background-color: #fafafa;z-index: 10;">
                        <div>
                            <span style="width:100%;">
                                <button style="width:48%;" type="button" class="btn-include active" onclick="trigger_include('<?php echo $taxonomy; ?>')">Include</button>
                                <button style="width:48%;" type="button" class="right btn-exclude" onclick="trigger_exclude('<?php echo $taxonomy; ?>')">Exclude</button>
                            </span>
                        </div>
                        <div><input placeholder="Search" class="search-tags"></input></div>
                    </div>
                    <div style="padding:0 24px;">
                        <div class="row" style="margin-bottom:0;">
                            <?php foreach ($terms as $term) { ?>
                              <label class="col s12 btn-hover" style="color:black !important;padding:10px;">
                                <input type="checkbox" value="<?php echo $term->term_id; ?>" <?php if(in_array($term->term_id,$get_in)){echo 'checked';}else if(in_array($term->term_id,$get_ex)){echo 'checked class="cross"';} ?> />
                                <span><?php echo $term->name; ?></span>
                              </label>
                            <?php } ?>
                        </div>
                    </div>
                    <div style="text-align:right;padding:15px 20px;position: sticky;bottom: 0;background-color: #fafafa;z-index: 10;">
                        <button type="button" onclick="clear_checkboxes('<?php echo $taxonomy; ?>');blur(this);" class="left btn-flat btn-clear">Clear</button>
                        <button type="button" onclick="close_custom_modal();" class="btn">OK</button>
                    </div>
                  </div>
                <?php } else { ?>
    				<div class="row">
    					<div class="col s12">
    						<label><?php echo $label; ?></label>
                            <select name="<?php echo $taxonomy; ?>" class="browser-default" id="<?php echo $taxonomy; ?>">
                                <option value="">All </option>
                                <?php foreach($terms as $term){ ?>
                                    <option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option>
                                <?php } ?>
                            </select>
    					</div>
    				</div>
                <?php } ?>
			<?php } ?>
			<div class="row" style="padding: 0 25px;"><div id="words-slider"></div></div>
		</div>
		<div style="padding:24px;text-align:right;">
		    <button onclick="reset_settings();blur(this);" type="button" style="margin-right:10px;" class="left btn-flat btn-clear">Reset</button>
		    <button onclick="search_settings('close');" type="button" style="margin-right:10px;" class="btn-flat btn-clear">Close</button>
			<button class="waves-effect waves-light btn-small" id="search-btn" type="submit">Search</button>
		</div>
	</form>
</div>
<script>

    jQuery(".search-tags").keyup(function(event){
        var search = this.value.toLowerCase();
        var invalidKeys = [13,38,40,39,37,33,34,16,17,255,18,20,9];
        if (invalidKeys.indexOf(event.keyCode) != -1 || search == ''){
            return;
        }
        var modal_id = jQuery(this).parents('.custom-modal')[0].id;
        var boxes = jQuery('#' + modal_id + ' [type=checkbox] + span');
        for (g = 0; g < boxes.length; g++) {
            if (boxes[g].innerHTML.toLowerCase().search(search) != -1){
                jQuery('#' + modal_id).animate({
                    scrollTop: boxes[g].offsetTop-112
                }, 0);
                break;
            }
        }
        
    });
    function trigger_include(group){
        jQuery('#' + group + '-select [type=checkbox]:not(:checked)').removeClass('cross');
        jQuery('#' + group + '-select').attr('select_status','');
        jQuery('#' + group + '-select' + ' .btn-include').addClass('active');
        jQuery('#' + group + '-select' + ' .btn-exclude').removeClass('active');
        
    }
    function trigger_exclude(group){
        jQuery('#' + group + '-select [type=checkbox]:not(:checked)').addClass('cross');
        jQuery('#' + group + '-select').attr('select_status','cross');
        jQuery('#' + group + '-select' + ' .btn-include').removeClass('active');
        jQuery('#' + group + '-select' + ' .btn-exclude').addClass('active');
    }
    function clear_checkboxes(group){
        jQuery('#' + group + '-select [type=checkbox]:checked').prop("checked", false);
    }
    jQuery("div[id$=-select].custom-modal [type=checkbox]").change(function() {
        if (this.checked == false){
            this.className = jQuery(this).parents('div[id$=-select].custom-modal').attr('select_status');
        }
    });
    function get_selected(group,output){
        var all = jQuery('#' + group + '-select [type=checkbox]:checked');
        var exclude = [];
        var include = [];
        if (output == 'value'){
            for (g = 0; g < all.length; g++) {
                if (jQuery(all[g]).hasClass('cross') == true){
                    exclude.push(all[g].value);
                }
                else{
                    include.push(all[g].value);
                }            
            }
        }
        else if (output == 'name'){
            for (g = 0; g < all.length; g++) {
                if (jQuery(all[g]).hasClass('cross') == true){
                    exclude.push(jQuery(all[g]).siblings('span')[0].innerHTML);
                }
                else{
                    include.push(jQuery(all[g]).siblings('span')[0].innerHTML);
                }            
            }
        }
        return {included:include,excluded:exclude};
        
    }
</script>
<script type = "text/javascript">
    function reset_settings(){
    	var tax = ['genre','character','pairing','tag','fandom'];
        for (i = 0; i < tax.length; i++) {
        	clear_checkboxes(tax[i]);
            jQuery('#' + tax[i] + '_show .chips_s').children().remove();
            jQuery('#' + tax[i] + '_show .all_label').children().remove();
            jQuery('#' + tax[i] + '_show .all_label').append('<div class="grey-text">All</div>');
        }
        var tax = ['rating','language','status'];
        for (i = 0; i < tax.length; i++) {
        	jQuery('#' + tax[i]).val('');
        }
        jQuery('#sort').val('modified/DESC');
        jQuery('#search').val('');
        slider.noUiSlider.reset();
    }
    function trigger_custom_modal(id){
        var elem = document.getElementById(id);
        var dsply = jQuery(elem).css('display');
        if (dsply == 'none'){
            jQuery(elem).css('display','block');
            jQuery('#custom_overlay').css('display','block');
        }
        else if (dsply == 'block'){
            close_custom_modal();
        }
    }
    function close_custom_modal(){
        var elems = jQuery('[id$="-select"]');
        for (i = 0; i < elems.length; i++) {
            if (jQuery(elems[i]).css('display') == 'block'){
                this_elem = elems[i];
                break;
            }
        }
        var group = this_elem.id.replace('-select','');
        var selected = get_selected(group,'name');
        jQuery('#' + group + '_show .chips_s').children().remove();
        jQuery('#' + group + '_show .all_label').children().remove();
        if (selected.included.length == 0 && selected.excluded.length == 0){
            jQuery('#' + group + '_show .all_label').append('<div class="grey-text">All</div>');
        }
    	for (i = 0; i < selected.included.length; i++) {
    	    jQuery('#' + group + '_show .included_chips').append('<div class="chip">' + selected.included[i] + '</div>');
    	}
    	for (i = 0; i < selected.excluded.length; i++) {
    	    jQuery('#' + group + '_show .excluded_chips').append('<div class="chip">' + selected.excluded[i] + '</div>');
    	}
    	jQuery(this_elem).css('display','none');
        jQuery('#custom_overlay').css('display','none');
        
    }

	jQuery("form[name='search']").submit(function(event) {
		event.preventDefault();
		document.getElementById('box').innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
		document.getElementById('pagination-wrapper').innerHTML = '';
		jQuery("#box").addClass("center-align");
		jQuery("#search-btn").addClass("disabled");
		search_settings('close');
		var search = jQuery('#search').val();
		var sort = jQuery('#sort').val();
		var rating = jQuery('#rating').val();
		if (rating != null && rating != ''){
		    rating = {included: [rating],excluded: []};
		}
		else{
		    rating = {included: [],excluded: []};
		}
		var language = jQuery('#language').val();
		if (language != null && language != ''){
		    language = {included: [language],excluded: []};
		}
		else{
		    language = {included: [],excluded: []};
		}
		var status = jQuery('#status').val();
		if (status != null && status != ''){
		    status = {included: [status],excluded: []};
		}
		else{
		    status = {included: [],excluded: []};
		}
		var fandom = get_selected('fandom','value');
		var genre = get_selected('genre','value');
		var character = get_selected('character','value');
		var pairing = get_selected('pairing','value');
		var tag = get_selected('tag','value');
		var words = document.getElementById('words-slider');
		var extras = document.getElementById('search_extras').innerHTML;
		words = slider.noUiSlider.get();
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/search.php',
			type: 'post',
			data: {ajax: 1,search:search,rating:rating,language:language,status:status,genre:genre,character:character,pairing:pairing,words:words,sort:sort,category:fandom,tag:tag,extras:extras},
			success: function(response){
				jQuery("#box").removeClass("center-align");
				jQuery("#search-btn").removeClass("disabled");
				var response_arr  = response.split('<div id="split_max_pages_count"></div>');
				var next_new = '<li class="disabled"><a>...</a></li><li class="waves-effect"><a onclick="paginate(' + response_arr[0] + ')">' + response_arr[0] + '</a></li><li class="waves-effect"><a onclick="paginate(2)"><i class="material-icons">chevron_right</i></a></li>';
				if (response_arr[0] == 1){
					next_new = '<li class="disabled"><a><i class="material-icons">chevron_right</i></a></li>';
				}
				if (response_arr[0] != 0){
					document.getElementById('pagination-wrapper').innerHTML = '<ul class="paginationm center-align"><li class="disabled"><a><i class="material-icons">chevron_left</i></a></li><li class="active disabled"><a>1</a></li>' + next_new + '</ul>';
				}
				document.getElementById('box').innerHTML = response_arr[1];
				var construct = '?words=' + parseInt(words[0].replace(',','').replace(',','')) + '%2C' + parseInt(words[1].replace(',','').replace(',',''));
				construct += '&sort=' + sort;
				if (search != ''){
					construct += '&search=' + search;
				}
				var tags_list = ['character','pairing','rating','tag','genre','status','language','fandom'];
                for (i = 0; i < tags_list.length; i++) {
                    var current_var = eval(tags_list[i]);
                    if (current_var.included.length != 0){
                        construct += '&' + tags_list[i] + '_included=' + current_var.included;
                    }
                    if (current_var.excluded.length != 0){
                        construct += '&' + tags_list[i] + '_excluded=' + current_var.excluded;
                    }
                }
				construct += '&page=1';
				window.history.pushState("object or string", document.getElementsByTagName("title")[0].innerHTML,construct);
			}
		});
	});
</script>
<style>
	.noUi-tooltip span {
		width: fit-content;
		text-align: center;
		color: black;
		font-size: 12px;
		opacity: 0;
		position: absolute;
		top: 40px;
		left: -50px;
		transition: opacity .25s cubic-bezier(.215,.61,.355,1);
	}
</style>
