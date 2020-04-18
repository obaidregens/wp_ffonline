<?php
function edit_chapter($chapter){
	if (! isset($_SESSION)){
		session_start();
	}
    $_SESSION['nonce_key'] = bin2hex(random_bytes(31));
    ?><input type="hidden" id="v_key" value="<?php echo $_SESSION['nonce_key']; ?>"><?php
?>
    <style>
        @media (any-hover: hover){
            .editorWrap button:hover::before {
                content: "" attr(tooltip) "";
                position: absolute;
                width: max-content;
                top: -50px;
                background: #555;
                padding: 10px;
                color: white;
                left: -38.5%;
                min-width:70px;
                border-radius: 5px;
              
            }
            .editorWrap button:hover::after{
            	content: '';
                width: 0;
                position: absolute;
                height: 0;
                left: 37%;
                top: -16px;
                border-left: 7px solid transparent;
                border-right: 7px solid transparent;
                border-top: 10px solid #555;
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
            background-color:white;
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
	<!-- New Post Form -->
	<?php
	if(!is_string($chapter)){}
	else if (strpos($chapter,'new-') !== false){
	    $book_id = explode('-',$chapter)[1];
	    $chapter = 'new';
	}
	else if ($chapter->post_status == 'future'){
		echo '<span id="get_utc" style="display:none;" >' . $chapter->post_date . '</span>';
		echo 'This chapter will be published in <span id="scheduled_timer"></span>.';
	}
	?>
	<form name="editchapter" class="col s12">
		<input type="hidden" name="chapter-id" value="<?php if($chapter == 'new'){echo 'new';}else{echo $chapter->ID;} ?>">
		<?php
		if ($chapter == 'new'){
		    ?><input type="hidden" name="book-id" value="<?php echo 'new-' . $book_id; ?>"><?php
		}
		?>
		<ul class="collapsible">
			<li class="active">
				<div class="collapsible-header">Chapter</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
							<textarea id="chapter_title" name="chapter_title" class="materialize-textarea"><?php if($chapter == 'new'){}else{echo $chapter->post_title;} ?></textarea>
							<label for="chapter_title">Chapter Title*</label>
						</div>
					</div>
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
							        <div class="editor" spellcheck="true" name="chapter_content" id="chapter_content" contenteditable="true"><?php if($chapter == 'new'){}else{echo $chapter->post_content;} ?></div>
							        <div class="word-count"><?php if($chapter == 'new'){ ?>0 Words<?php }else{echo str_word_count($chapter->post_content) . ' Words';} ?></div>
							    </div>
							    <label for="chapter_content">Chapter*</label>
						</div>
					</div>
					<div class="row">
						<div class="switch center-block" style="padding-left:10px;">
							<label>
								<label id="comments-label"><?php if ($chapter != 'new' && $chapter->comment_status == 'open'){echo 'Reviews Enabled';}else{echo 'Reviews Disabled';}?></label>
								<input onclick="change_comment_label()" id='comments' name='comments' type='checkbox' value='open' <?php if ($chapter != 'new' && $chapter->comment_status == 'open'){echo 'checked';}?>>
								<span class='lever'></span>
							</label>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Author Notes</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
							<textarea name="pre_chapter" id="pre_chapter" class="materialize-textarea"><?php if($chapter == 'new'){}else{echo get_post_meta($chapter->ID,'post-chapter_notes')[0]; } ?></textarea>
							<label for="pre_chapter">Pre-Chapter Notes</label>
						</div>
					</div>
					<div class="row">
						<div class="input-field col s12">
							<textarea name="post_chapter" id="post_chapter" class="materialize-textarea"><?php if($chapter == 'new'){}else{echo get_post_meta($chapter->ID,'post-chapter_notes')[0];} ?></textarea>
							<label for="post_chapter">Pre-Chapter Notes</label>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Schedule Chapter</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="switch">
							<label>
								<input <?php if($chapter == 'new'){}else{if ($chapter->post_status == 'draft' || $chapter->post_status == 'future'){echo 'disabled';}}?> onclick="schedule_switch()" id='schedule-switch' name='schedule-switch' type='checkbox' value='schedule'>
								<span class='lever'></span>
								<label>Schedule chapter to be published later.</label>
							</label>
						</div>
					</div>
					<div id="scheduled-input-wrapper" class="row">
					</div>
				</div>
			</li>
		</ul>
		<div class="switch center-block" style="padding-left:10px;">
			<label>
				<label id="publish-label"><?php if ($chapter != 'new' && $chapter->post_status == 'publish'){echo 'Publish';}else{echo 'Draft';}?></label>
				<input onclick="change_publish_label()" <?php if ($chapter != 'new' && get_post($chapter->post_parent)->post_status == 'draft'){echo 'disabled';} ?> id="publish" name='publish' type='checkbox' value='publish' <?php if ($chapter != 'new' && $chapter->post_status == 'publish'){echo 'checked';}?>>
				<span class='lever'></span>
			</label>
		</div>

		<div id="button-wrapper" style="padding-right: 15px;" class="right"><button class="waves-effect waves-light btn-small" type="submit">Save</button></div>
	</form>
    <script type = "text/javascript">
		jQuery("#chapter_content").focus(function(){
			jQuery(this).siblings('.toolbar').children('button[text_action=justifyLeft]').click();
			document.execCommand('formatblock',false,'p');
		});
        jQuery('.editorWrap .editor').keyup(function(event){
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
        });
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

			var id = jQuery("input[name='chapter-id']").val();
			if (id == 'new'){
			    var id = jQuery("input[name='book-id']").val();
			}
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
				document.getElementById("button-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
				var title = jQuery('#chapter_title').val();
				var content = jQuery('#chapter_content')[0].innerHTML;
				var pre = jQuery('#pre_chapter').val();
				var post = jQuery('#post_chapter').val();
				if (document.querySelector("#publish").checked == true){
					var publish = 'publish';
				}
				else{
					var publish = 'draft';
				}
				if (document.querySelector("#schedule-switch").checked == true){
					var date = jQuery('#dateinput').val();
					var time = jQuery('#timeinput').val();
					var current = new Date();
					var offset = current.getTimezoneOffset();
				}
				else{
					var date = 0;
					var time = 0;
				}	
				if (document.querySelector("#comments").checked == true){
					var comments = 'open';
				}
				else{
					var comments = 'closed';
				}
				jQuery.ajax({
					url: '/wp-content/themes/book-writer/write/php/edit-chapter.php',
					type: 'post',
					data: {ajax: document.getElementById('v_key').value,id:id,title:title,content:content,publish:publish,pre:pre,post:post,date:date,time:time,offset:offset,comments:comments},
					success: function(response){
						console.log(response);
						if (response == '1'){
							document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
							M.Toast.dismissAll();
							M.toast({html: 'Chapter Published.'});
						}
						else if (response == '2'){
							document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
							M.Toast.dismissAll();
							M.toast({html: 'Chapter Saved.'});
						}
						else if (response == '4'){
							document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
							M.Toast.dismissAll();
							M.toast({html: 'Your chapter has been scheduled for posting.'});
						}
						else if (response == '3' || response == '9'){
							document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
							M.Toast.dismissAll();
							M.toast({html: 'There was a problem saving your chapter.'});
						}
						else{
							document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
							M.Toast.dismissAll();
							M.toast({html: 'An unknown error occured.'});
						}
					}
				});
			}
			
		});
		function change_comment_label(){
			if (document.querySelector("#comments-label").innerHTML == 'Reviews Enabled'){
				document.querySelector("#comments-label").innerHTML = 'Reviews Disabled';
			} 
			else{
				document.querySelector("#comments-label").innerHTML = 'Reviews Enabled';
			}
		}
		function change_publish_label(){
			if (document.querySelector("#publish-label").innerHTML == 'Publish'){
				document.querySelector("#publish-label").innerHTML = 'Draft';
			}
			else{
				document.querySelector("#publish-label").innerHTML = 'Publish';
			}
		}
    </script>
<?php }