<main>

	<?php
	//PHP - Preperation of data
	//Use <?= $var to echo in HTML
	if (! headers_sent() && ! isset($_SESSION) ){
		session_start();
	}

	$book_id = page_chain_arr[1];
	$chapter_id = page_chain_arr[2];
	
	$book = get_post($book_id);
	$chapter = get_post($chapter_id);
	

	if ($chapter_id == 'new'){
		$title = '';
		$content = '';
		$comment_status = 'checked';
		$pre_chapter = '';
		$post_chapter = '';
		$publish_status = '';
	}
	else{
		$future_countdown = '';
		if ($chapter->post_status == 'future'){
			$future_countdown = '<span id="get_utc" style="display:none;">' . $chapter->post_date . '</span>';
			$future_countdown.= 'This chapter will be published in <span id="scheduled_timer"></span>.';
		}

		$title = $chapter->post_title;
		$content = $chapter->post_content;
		$comment_status = 'checked';
		if ($chapter->comment_status == 'closed'){
			$comment_status = '';
		}

		$pre_chapter = get_post_meta($chapter_id,'pre-chapter_notes',true);
		$post_chapter = get_post_meta($chapter_id,'post-chapter_notes',true);
		$publish_status = 'checked';
		if (in_array($chapter->post_status,array('draft','future'))){
			$publish_status = '';
		}
	}
	$publish_allowed = '';
	$schedule_allowed = '';
	if ($book->post_status == 'draft'){
		$publish_allowed = 'disabled';
		$schedule_allowed = 'disabled';
	}


	?>
	<!-- HTML STARTS -->
	<input type="hidden" name="book_id" value="<?= $book_id ?>">
	<input type="hidden" name="chapter_id" value="<?= $chapter_id ?>">
	<a onclick="load_page('write')" class="row btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a>
	<form name="editchapter" class="mobile-margin col s12">
		<!-- Chapter Title -->
		<div class="row">
			<div class="input-field col s12">
				<input id="chapter_title" name="chapter_title" type="text" data-length="40" value="<?= $title ?>" class="validate valid">
				<label for="chapter_title">Chapter Title*</label>
			</div>
		</div>
		<!-- Chapter Editor -->
		<div class="row">
			<div class="input-field col s12">
				<div class="top-bar"><a href="#!" class="autosave-trigger">Autosaves</a></div>
				<div class="editorWrap">
					<div class="toolbar">
						<button tabindex="-1" tooltip="Ctrl B" text_action="bold" type="button"><i class="fas fa-bold"></i></button>
						<button tabindex="-1" tooltip="Ctrl I" text_action="italic" type="button"><i class="fas fa-italic"></i></button>
						<button tabindex="-1" tooltip="Ctrl U" text_action="underline" type="button"><i class="fas fa-underline"></i></button>
						<button tabindex="-1" tooltip="Ctrl L" text_action="justifyLeft" type="button"><i class="fas fa-align-left"></i></button>
						<button tabindex="-1" tooltip="Ctrl E" text_action="justifyCenter" type="button"><i class="fas fa-align-center"></i></button>
						<button tabindex="-1" tooltip="Ctrl R" text_action="justifyRight" type="button"><i class="fas fa-align-right"></i></button>
						<button tabindex="-1" tooltip="Ctrl D" text_action="strikethrough" type="button"><i class="fas fa-strikethrough"></i></button>
						<button tabindex="-1" tooltip="Ctrl Z"text_action="undo" type="button"><i class="fas fa-undo-alt"></i></button>
						<button tabindex="-1" tooltip="Ctrl Y" text_action="redo" type="button"><i class="fas fa-redo-alt"></i></button>
						<button tabindex="-1" tooltip="F11" text_action="fullscreen" type="button"><i class="fas fa-expand"></i></button>
					</div>
					<div class="editor" spellcheck="true" name="chapter_content" id="chapter_content" contenteditable="true">
						<?= $content ?>
					</div>
					<div class="word-count"></div>
				</div>
				<label for="chapter_content">Chapter*</label>
			</div>
		</div>
		<!-- Reviews Enable/Disable -->
		<div class="row" style="padding-left:20px;" >
			<div class="switch center-block">
				<label>
					<label></label>
					<input on_label="Reviews Enabled" off_label="Reviews Disabled" id='comments' name='comments' type='checkbox' value='open' <?= $comment_status ?>>
					<span class='lever'></span>
				</label>
			</div>
		</div>
		<div class="switch center-block" style="padding-left:10px;">
			<label>
				<label></label>
				<input on_label="Publish" off_label="Draft" id="publish" name='publish' type='checkbox' value='publish' <?= $publish_allowed, ' ', $publish_status ?> >
				<span class='lever'></span>
			</label>
		</div>
		<div id="button-wrapper" style="padding-right: 15px;" class="right-align"><button class="waves-effect waves-light btn-small" type="submit">Save</button></div>
	</form>
	<div id="autosave-modal" class="modal modal-large">
		<div class="modal-content">
			<h3 class="autosaves-header">Autosaves</h3>
			<div class="autosaves-list"></div>
		</div>
	</div>
	<script type = "text/javascript">
	var intervalID;
	function load_chapter_part(){
		let autosaves = {};
		jQuery('input[type="checkbox"][on_label][off_label]').trigger('change');
		jQuery(document).ready(function(){
			jQuery("#chapter_content").focus();
			jQuery(this).siblings('.toolbar').children('button[text_action=justifyLeft]').click();
			document.execCommand('formatblock',false,'p');
		});
		function count_words(){
            var val = this.innerHTML.replace(/ &/g,'&');
            val = val.replace(/&nbsp;/g,' ');
            val = val.replace(/(<([^>]+)>)/ig,"");
            if (val == ''){
                jQuery(this).siblings('.word-count')[0].innerHTML = '0 Words';
            }
            else{
                var val_arr = preg_split('/[^A-Za-z0-9_\']+/',val);
                jQuery(this).siblings('.word-count')[0].innerHTML = val_arr.length + ' Words';
            }
        }
		jQuery('.editorWrap .editor').keyup(count_words);
		jQuery('.editorWrap .editor').each(count_words);
        jQuery('.editorWrap .editor').keydown(function(event){
            if (event.keyCode == 122){
                event.preventDefault();
                jQuery(this).siblings('.toolbar').children('button[text_action=fullscreen]').click();
            }
            if (event.keyCode == 13){
                event.preventDefault();
				document.execCommand('formatblock',false,'p');
            	document.execCommand('insertParagraph');
            }
            if (event.ctrlKey == true){
				if ([67,86,88,65].includes(event.keyCode)){
					return;
				}
                event.preventDefault();
                if (event.keyCode == 65){
                    document.execCommand('selectAll');
                }
                if (event.keyCode == 66){
                    jQuery(this).siblings('.toolbar').children('button[text_action=bold]').click();
                }
                else if (event.keyCode == 73){
                    jQuery(this).siblings('.toolbar').children('button[text_action=italic]').click();
                }
                else if (event.keyCode == 85){
                    jQuery(this).siblings('.toolbar').children('button[text_action=underline]').click();
                }
                else if (event.keyCode == 76){
                    jQuery(this).siblings('.toolbar').children('button[text_action=justifyLeft]').click();
                }
                else if (event.keyCode == 69){
                    jQuery(this).siblings('.toolbar').children('button[text_action=justifyCenter]').click();
                }
                else if (event.keyCode == 82){
                    jQuery(this).siblings('.toolbar').children('button[text_action=justifyRight]').click();
                }
                else if (event.keyCode == 68){
                    jQuery(this).siblings('.toolbar').children('button[text_action=strikethrough]').click();
                }
                else if (event.keyCode == 90){
                    jQuery(this).siblings('.toolbar').children('button[text_action=undo]').click();
                }
                else if (event.keyCode == 89){
                    jQuery(this).siblings('.toolbar').children('button[text_action=redo]').click();
                }
            }
        });
        jQuery(".editorWrap button").click(function() {
            var editor_elem = jQuery(this).parents('.editorWrap')[0];
            var action = jQuery(this).attr('text_action');
            if (action == 'fullscreen'){
                if (document.fullscreenElement){
                    document.exitFullscreen();
                }
                else{
                    editor_elem.requestFullscreen();
                }
            }
            else{
                if (action.includes('justify')){
                    jQuery('[text_action^="justify"]').removeClass('active');
                }
            	document.execCommand(action);
            	if (document.queryCommandValue(action) == 'true'){
            	    jQuery(this).addClass('active');
            	}
            	else {
            	    jQuery(this).removeClass('active');
            	}
            }

		});
		jQuery("form[name='editchapter']").submit(function(event) {
			event.preventDefault();
			M.Toast.dismissAll();

    		var error = 0;
    		if( document.querySelector("#chapter_title").value.replace(/\s+/g, '') == "" && document.querySelector("#publish").checked == true) {
    			error = 1;
				M.toast({html: 'Your Chapter has to have a title.'});
			}
			if( document.querySelector("#chapter_title").value.replace(/\s+/g, '').length > 40 && document.querySelector("#publish").checked == true) {
    			error = 1;
				M.toast({html: 'Title cannot be of more than 40 characters.'});
    		}
    		if(document.querySelector("#chapter_content").innerHTML.replace(/\s+/g, '') == "" && document.querySelector("#publish").checked == true) {
    			error = 1;
				M.toast({html: 'Why do you want to publish an empty chapter?'});
    		}
			if (error == 0){
				spin("#button-wrapper");
				var data_submit = {
					book_id: jQuery("input[name='book_id']").val(),
					chapter_id: jQuery("input[name='chapter_id']").val(),
					title: jQuery('#chapter_title').val(),
					content: jQuery('#chapter_content')[0].innerHTML,
					publish: document.getElementById('publish').checked,
					comments: document.getElementById('comments').checked
				};
				
				api('submit_chapter',{
					dataType: 'JSON',
					data: data_submit,
					callback: function(response){
						M.Toast.dismissAll();
						document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
						if (response.code == 1){
							M.toast({html: 'Chapter Published.'});
						}
						else if (response.code == 2){
							M.toast({html: 'Chapter Saved.'});
						}
						else if (response.code > 5){
							M.toast({html: 'There was a problem saving your chapter.'});
						}
						else{
							M.toast({html: 'An unknown error occured.'});
						}
						if ((response.code <= 5) && data_submit.chapter_id == 'new'){
							window.location.href = document.location.origin + '/dashboard/write/' + data_submit.book_id + '/' + response.chapter_id;
						}
					}
				});
			}
			
		});
		//Autosaves
		autosave_content();
		jQuery('.autosave-trigger').click(function(){
			jQuery('#autosave-modal').modal('open');
		});
		jQuery(document).on('click','.autosave-edit a',function(){
			autosave_content();
			let timestamp = this.parentElement.parentElement.getAttribute('timestamp');
			jQuery('#chapter_content')[0].innerHTML = autosaves[timestamp];
		});
		function add_autosaves(autosaves_new){
			clear_autosaves();
			autosaves = autosaves_new;
			let autosave_keys = Object.keys(autosaves);
			autosave_keys.sort();
			autosave_keys.reverse();
			for (let i = 0; i < autosave_keys.length; i++) {
				let timestamp = autosave_keys[i];
				let time = timestamp_to_local(timestamp);
				let content = autosaves[timestamp];
				if (content.length > 400){
					content = content.substring(0,400) + ' <a href="#!">...</a>';
				}
				jQuery('.autosaves-list').append('<div class="autosave" timestamp="' + timestamp + '"><div class="autosave-edit"><a>Edit</a></div><div class="autosave-time">' + time + '</div><div class="autosave-short-content">' + content + '</div></div>');				
			}
		}
		function clear_autosaves(){
			autosaves = {};
			jQuery('.autosaves-list').children().remove();
		}
		function autosave_content() {
			if (document.querySelector("#chapter_content").innerHTML.replace(/\s+/g, '') == ""){
				return;
			}
			api('autosave_content',{
				dataType: 'JSON',
				data: {
					content: jQuery('#chapter_content')[0].innerHTML,
					book_id: jQuery("input[name='book_id']").val(),
					chapter_id: jQuery("input[name='chapter_id']").val()
				},
				callback: function(response){
					add_autosaves(response);
				}
			});   
    	}
		intervalID = setInterval(autosave_content, 10000);
		jQuery('textarea[data-length],input[data-length][type="text"]').characterCounter();
	}
	load_chapter_part();
    </script>
</main>