<style>
        @media only screen and (min-width: 600px){
			.editorWrap .toolbar {
				overflow: visible !important;
			}
        }
        .editorWrap + label{
            transform:none;
            left:15px;
            top:-27px;
        }
        .editorWrap{
            border:1px solid #ccc;
        }
        .editorWrap:fullscreen {
            background-color:var(--background-color);
            color: var(--text-color);
        }
        .editorWrap:fullscreen .editor{
            max-height:calc(100vh - 75px) !important;
            height:calc(100vh - 75px);
        }
        .editorWrap .toolbar{
            font-size:0 !important;
            border-bottom:1px solid #ccc;
            white-space: nowrap;
            overflow: auto;
        }
        .editorWrap .editor p{
            margin:0 !important;
            min-height:21px;
        }
        .editorWrap button{
            position:relative;
            outline:none !important;
            background-color:transparent;
            color:var(--text-color);
            border-radius:0px;
        }
        .editorWrap button.active,.editorWrap button:hover{
            background-color:var(--theme-color);
            color:var(--background-color);
        }
        .editorWrap .word-count{
            height: 34px;
            border-top:1px solid #ccc;
            padding: 5px;
            color: grey;
        }
        .editorWrap .editor{
            min-height:100px;
            outline:none !important;
            padding:10px;
            max-height: 500px;
            overflow: auto;
        }
    </style>
<main>

	<?php
	//PHP - Preperation of data
	//Use <?= $var to echo in HTML
	if (! headers_sent() && ! isset($_SESSION) ){
		session_start();
	}
	$_SESSION['nonce_key'] = bin2hex(random_bytes(31));

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
	<input type="hidden" id="v_key" value="<?= $_SESSION['nonce_key'] ?>">
	<input type="hidden" name="book_id" value="<?= $book_id ?>">
	<input type="hidden" name="chapter_id" value="<?= $chapter_id ?>">
	
	<a onclick="load_page('write')" class="row btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a>
	<form name="editchapter" class="mobile-margin col s12">
		<!-- Chapter Title -->
		<div class="row">
			<div class="input-field col s12">
				<textarea id="chapter_title" name="chapter_title" class="materialize-textarea"><?= $title ?></textarea>
				<label for="chapter_title">Chapter Title*</label>
			</div>
		</div>
		<!-- Chapter Editor -->
		<div class="row">
			<div class="input-field col s12">
				<div class="editorWrap">
					<div class="toolbar">
						<button tooltip="Ctrl B" text_action="bold" type="button"><i class="fas fa-bold"></i></button>
						<button tooltip="Ctrl I" text_action="italic" type="button"><i class="fas fa-italic"></i></button>
						<button tooltip="Ctrl U" text_action="underline" type="button"><i class="fas fa-underline"></i></button>
						<button tooltip="Ctrl L" text_action="justifyLeft" type="button"><i class="fas fa-align-left"></i></button>
						<button tooltip="Ctrl E" text_action="justifyCenter" type="button"><i class="fas fa-align-center"></i></button>
						<button tooltip="Ctrl R" text_action="justifyRight" type="button"><i class="fas fa-align-right"></i></button>
						<button tooltip="Ctrl D" text_action="strikethrough" type="button"><i class="fas fa-strikethrough"></i></button>
						<button tooltip="Ctrl Z"text_action="undo" type="button"><i class="fas fa-undo-alt"></i></button>
						<button tooltip="Ctrl Y" text_action="redo" type="button"><i class="fas fa-redo-alt"></i></button>
						<button tooltip="F11" text_action="fullscreen" type="button"><i class="fas fa-expand"></i></button>
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

		<div id="button-wrapper" style="padding-right: 15px;" class="right"><button class="waves-effect waves-light btn-small" type="submit">Save</button></div>
	</form>
	<script type = "text/javascript">
		jQuery('input[type="checkbox"][on_label][off_label]').trigger('change');
		jQuery("#chapter_content").focus(function(){
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
            jQuery(editor_elem).children('.editor').focus();
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
		jQuery(document).ready(function(){
			if (document.getElementById('get_utc') != null){
				var date_utc = document.getElementById('get_utc').innerHTML;
				var current = new Date();
				var offset = current.getTimezoneOffset();
				var countDownDate = new Date(date_utc + ' UTC');

				// Update the count down every 1 second
				var x = setInterval(function() {

					// Get today's date and time
					var now = new Date().getTime();

					// Find the distance between now and the count down date
					var distance = countDownDate - now;

					// Time calculations for days, hours, minutes and seconds
					var days = Math.floor(distance / (1000 * 60 * 60 * 24));
					var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
					var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
					var seconds = Math.floor((distance % (1000 * 60)) / 1000);

					// Output the result in an element with id="demo"
					document.getElementById("scheduled_timer").innerHTML = '';
					if (days  == 1){
						document.getElementById("scheduled_timer").innerHTML += days + " day ";
					}
					else if (days != 0 && hours == 0){
						document.getElementById("scheduled_timer").innerHTML += days + " days";
					}
					else if (days != 0){
						document.getElementById("scheduled_timer").innerHTML += days + " days ";
					}
					if (hours == 1){
						document.getElementById("scheduled_timer").innerHTML += hours + " hour";
					}
					else if (hours != 0){
						document.getElementById("scheduled_timer").innerHTML += hours + " hours";
					}
					if (minutes != 0 && hours == 0 && days == 0){
						document.getElementById("scheduled_timer").innerHTML += minutes + " minutes ";
					}

					// If the count down is over, write some text 
					if (distance < 0) {
						clearInterval(x);
						document.getElementById("scheduled_timer").innerHTML = "Chapter Published!";
					}
				}, 1000);
			}
		});
		function schedule_switch(){
			if (document.querySelector("#schedule-switch").checked == true){
				jQuery('#scheduled-input-wrapper').append('<div class="input-field col s6"><input type="text" id="dateinput" class="datepicker"><label for="dateinput" class="">Date</label></div><div class="input-field col s6"><input type="text" id="timeinput" class="timepicker"><label for="dateinput" class="">Time</label></div>');
				var tomorrow = new Date();
				tomorrow.setDate(new Date().getDate()+1);
				var max = new Date();
				max.setDate(new Date().getDate()+6);
				jQuery('#dateinput').datepicker({
					format: 'yyyy-mm-dd',
					minDate: tomorrow,
					maxDate: max,

				});
				jQuery('#timeinput').timepicker();

			}
			else{
				jQuery('#scheduled-input-wrapper').children().remove();
			}
		}
		jQuery("form[name='editchapter']").submit(function(event) {
			event.preventDefault();
			M.Toast.dismissAll();

    		var error = 0;
    		if( document.querySelector("#chapter_title").value.replace(/\s+/g, '') == "" && document.querySelector("#publish").checked == true) {
    			error = 1;
				M.toast({html: 'Your Chapter has to have a title.'});
    		}
    		if(document.querySelector("#chapter_content").innerHTML.replace(/\s+/g, '') == "" && document.querySelector("#publish").checked == true) {
    			error = 1;
				M.toast({html: 'Why do you want to publish an empty chapter?'});
    		}
			if (error == 0){
				spin("#button-wrapper");
				var data_submit = {
					ajax: document.getElementById('v_key').value,
					book_id: jQuery("input[name='book_id']").val(),
					chapter_id: jQuery("input[name='chapter_id']").val(),
					title: jQuery('#chapter_title').val(),
					content: jQuery('#chapter_content')[0].innerHTML,
					publish: document.getElementById('publish').checked,
					comments: document.getElementById('comments').checked
				};
				
				jQuery.ajax({
					url: '/wp-content/themes/book-writer/dashboard/ajax/submit-chapter.php',
					type: 'post',
					dataType: 'JSON',
					data: data_submit,
					success: function(response){
						console.log(response);
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
							window.location.href = document.location.origin + '/dashboard/write/' + response.chapter_id;
						}
					}
				});
			}
			
		});
    </script>
</main>