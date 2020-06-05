////HTML is located in Chapter Page Template
////Script included in 'footer.php'

//fontSize default
var originalfontSize = parseInt(jQuery(".chapter-content").css("font-size"));
		
//Line Height setting
jQuery(".entry-content").get(0).style.setProperty("line-height",cookies["line-height"] + "px");

//Font Size setting
jQuery(".chapter-content").get(0).style.setProperty("font-size",cookies["font-size"] + "px");


//Container Settings
if (cookies["line-height"]/cookies["font-size"] <= 1.2){jQuery("#decreaselineheight").get(0).style.setProperty("display", "none");}
else if (cookies["line-height"]/cookies["font-size"] >= 3.5){jQuery("#increaselineheight").get(0).style.setProperty("display", "none");}
if (cookies["font-size"] > 42){jQuery("#increasefontsize").get(0).style.setProperty("display", "none");}
else if (cookies["font-size"] < 10){jQuery("#decreasefontsize").get(0).style.setProperty("display", "none");}



jQuery("#increasefontsize").click
(
	function()
	{
		
		var lineHeight = parseInt(jQuery(".chapter-content").css("line-height"));
		var fontSize = parseInt(jQuery(".chapter-content").css("font-size"));
		if (fontSize <= 42){
			var newfontSize = fontSize + 4;
			lineHeight = ((lineHeight / (fontSize/originalfontSize)) * (newfontSize/originalfontSize));
			jQuery(".chapter-content").get(0).style.setProperty("font-size",newfontSize + "px");
			jQuery("#decreasefontsize").get(0).style.setProperty("display", "inline-block");
			jQuery(".chapter-content").get(0).style.setProperty("line-height",lineHeight + "px");
		}
		var fontSize = parseInt(jQuery(".chapter-content").css("font-size"));
		if (fontSize > 42){
			jQuery("#increasefontsize").get(0).style.setProperty("display", "none");
		}
		setCookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
		setCookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });
	}
	
);
jQuery("#decreasefontsize").click
(
	function() 
	{
		var lineHeight = parseInt(jQuery(".chapter-content").css("line-height"));
		var fontSize = parseInt(jQuery(".chapter-content").css("font-size"));
		if (fontSize >= 10){
			var newfontSize = fontSize - 4;
			lineHeight = ((lineHeight / (fontSize/originalfontSize)) * (newfontSize/originalfontSize));
			jQuery(".chapter-content").get(0).style.setProperty("font-size",newfontSize + "px");
			jQuery(".chapter-content").get(0).style.setProperty("line-height",lineHeight + "px");
			jQuery("#increasefontsize").get(0).style.setProperty("display", "inline-block");
		}
		var fontSize = parseInt(jQuery(".chapter-content").css("font-size"));
		if (fontSize < 10){
			jQuery("#decreasefontsize").get(0).style.setProperty("display", "none");
		}
		setCookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
		setCookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });				}
)
jQuery("#increaselineheight").click
(
	function()
	{
		var lineHeight = parseInt(jQuery(".entry-content").css("line-height"));
		var fontSize = parseInt(jQuery(".entry-content").css("font-size"));
		if (lineHeight/fontSize < 3.5){
			lineHeight = lineHeight + (4 * fontSize/originalfontSize);
			jQuery(".entry-content").get(0).style.setProperty("line-height",lineHeight + "px");
			jQuery("#decreaselineheight").get(0).style.setProperty("display", "inline-block");
		}
		var lineHeight = parseInt(jQuery(".entry-content").css("line-height"));
		if (lineHeight/fontSize >= 3.5){
			jQuery("#increaselineheight").get(0).style.setProperty("display", "none");
		}
		setCookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
		setCookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });				}
);
jQuery("#decreaselineheight").click
(
	function() 
	{
		var lineHeight = parseInt(jQuery(".entry-content").css("line-height"));
		var fontSize = parseInt(jQuery(".entry-content").css("font-size"));
		if (lineHeight/fontSize > 1.2){
			lineHeight = lineHeight - (4 * fontSize/originalfontSize);
			jQuery(".entry-content").get(0).style.setProperty("line-height",lineHeight + "px");
			jQuery("#increaselineheight").get(0).style.setProperty("display", "inline-block");
		}
		var lineHeight = parseInt(jQuery(".entry-content").css("line-height"));
		if (lineHeight/fontSize <= 1.2){
			jQuery("#decreaselineheight").get(0).style.setProperty("display", "none");
		}
		setCookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
		setCookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });				}
)

jQuery("#increasemargin").click
(
	function()
	{
		jQuery("#ans").text(jQuery('.chapter-content')[0].style.margin);
		var margin = jQuery('.chapter-content')[0].style.margin;
		margin = parseInt((margin.split(" ")[1]).replace("%",""));
		if (margin < 20){
			margin = margin + 3;
			jQuery(".chapter-content").get(0).style.setProperty("margin-right",margin + "%");
			jQuery(".chapter-content").get(0).style.setProperty("margin-left",margin + "%");
			jQuery("#decreasemargin").get(0).style.setProperty("display", "inline-block");
		}
		var margin = jQuery('.chapter-content')[0].style.margin;
		margin = parseInt((margin.split(" ")[1]).replace("%",""));

		if (margin >= 20){
			jQuery("#increasemargin").get(0).style.setProperty("display", "none");
			
		}
	}
);
jQuery("#decreasemargin").click
(
	function()
	{
		var margin = jQuery('.chapter-content')[0].style.margin;
		margin = parseInt((margin.split(" ")[1]).replace("%",""));
		if (margin > 2){
			margin = margin - 3;
			jQuery(".chapter-content").get(0).style.setProperty("margin-right",margin + "%");
			jQuery(".chapter-content").get(0).style.setProperty("margin-left",margin + "%");
			jQuery("#increasemargin").get(0).style.setProperty("display", "inline-block");
		}
		var margin = jQuery('.chapter-content')[0].style.margin;
		margin = parseInt((margin.split(" ")[1]).replace("%",""));
		if (margin <= 2){
			jQuery("#decreasemargin").get(0).style.setProperty("display", "none");
		}
	}
);


/////Other Chapter Scripts
//Materialize Intialization
jQuery('.collapsible').collapsible();
//Comments
jQuery('.dropdown-trigger').dropdown();
//Search Event Listener
window.addEventListener("keydown",function (e) {
    if ((e.ctrlKey && e.keyCode === 70)) {
        jQuery('#searchbook').modal('open');
        e.preventDefault();
    }
});
//Search Book
//Focus Search on Open
jQuery('#searchbook').modal({onOpenEnd: function(){
    jQuery('#search_book').focus();
}});
jQuery("#search_book").keyup(function(event){
	var invalidKeys = [13,38,40,39,37,33,34,16,17,255,18,20,9];
	if (invalidKeys.indexOf(event.keyCode) != -1){
		return;
	}
    if ((event.ctrlKey && event.keyCode === 70)) {
        jQuery('#searchbook').modal('close');
        event.preventDefault();
        return;
    }
	jQuery('#load_search_results')[0].innerHTML = '<div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div>';
	api('search_book_contents',{
		data: {chapter_id:jQuery('#chapter_id').val(),s:jQuery('#search_book').val()},
		callback: function(response){
			jQuery('#load_search_results')[0].innerHTML = response;
		}
	});    
});
//Text Highlight Handler
function define_text(){
	
	if (window.getSelection().anchorNode !== null && (jQuery(window.getSelection().anchorNode.parentElement).parents('#define').length != 0 || window.getSelection().anchorNode.parentElement.id == 'define')){}
	else if (window.getSelection().anchorNode === null || window.getSelection().toString() == '' || jQuery(window.getSelection().anchorNode.parentElement).parents('.chapter-content').length == 0){
		jQuery('#define').css('display','none');
		jQuery('#define-full').modal('close');
	}
	else{
		var define = window.getSelection().toString().split(' ')[0];
		jQuery.get('https://api.dictionaryapi.dev/api/v1/entries/en/' + define, function(response){
			var word = response[0].word;
			jQuery('#define .word').html(word);
			var types = Object.keys(response[0].meaning);
			var print = '';
			for (i = 0; i < types.length; i++) {
				print += '<div class="type"><div class="type-title">' + types[i] + '</div><div class="type-content"><ol>';
				for (j = 0; j < response[0].meaning[types[i]].length; j++) {
					if (i == 0 && j == 0){
						jQuery('#define .first_meaning').html(types[i] + ' - ' + response[0].meaning[types[i]][j].definition);
					}
					print += '<li style="margin:0 0 1.75em 0;">';
					print += '<p style="margin:0;" class="definition">' + response[0].meaning[types[i]][j].definition + '</p>';
					if (response[0].meaning[types[i]][j].example !== undefined){
						print += '<p style="margin:0;color:var(--secondary-color);" class="example">"' + response[0].meaning[types[i]][j].example + '"</p>';
					}
					print += '</li>';
				}
				print += '</ol></div></div>';
			}
			jQuery('#define-full .all-meanings')[0].innerHTML = print;
			var meaning = response[0].meaning[Object.keys(response[0].meaning)[0]][0].definition;
			jQuery('#define').css('display','block');
		});
	}
}
//jQuery(".chapter-content").mouseup(define_text);
//jQuery(".chapter-content").change(define_text);
//jQuery(".chapter-content").click(define_text);
document.addEventListener("selectionchange", define_text);


//Bookmarks
function bookmark_this(para,chapter){
	jQuery('#p-' + para + ' .bookmark-wrapper')[0].innerHTML = '<div class="loader" style="width:21px;height:21px;"></div>';
	api('bookmark_this',{
		data: {para:para,chapter:chapter},
		callback: function(response){
			jQuery('#p-' + para + ' .bookmark-wrapper')[0].innerHTML = '<i onclick="bookmark_this(' + para + ',\'' + chapter + '\')" class="btn-bookmark btn-favorite far fa-bookmark"></i>';
			if (response == 0){
				jQuery('#p-' + para + ' .btn-bookmark').removeClass('active');
			}
			else if (response == 1){
				jQuery('#p-' + para + ' .btn-bookmark').addClass('active');
			}
			else if (response == 3){
			    jQuery('#p-' + para + ' .btn-bookmark').removeClass('active');
			    jQuery('#login-modal').modal('open');
			}
		}
	});			
}

//Comments
function reply_to(id){
	if (id != 'cancel'){
		document.getElementById('reply-wrapper').innerHTML = '<div style="padding-bottom:2px;">Replying to ' + jQuery('#comment-' + id + ' .comment-author')[0].innerHTML + ' <a style="padding-left:4px;" onclick="reply_to(\'cancel\')">Cancel</a></div><blockquote style="margin:0;font-size:14px;">' + jQuery('#comment-' + id + ' .comment-content')[0].innerHTML + '</blockquote>';
		jQuery('#reply_id').val(id);
	}
	else{
		document.getElementById('reply-wrapper').innerHTML = '';
		jQuery('#reply_id').val(0);
	}
}
function submit_comment() {
	document.getElementById("submit-comment-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
	var comment = jQuery('#comment').val();
	var chapter_id = jQuery('#chapter_id').val();
	var data = {chapter_id:chapter_id,comment:comment,action:'insert'};
	var reply = jQuery('#reply_id').val();
	if (reply != 0){
		var data = {chapter_id:chapter_id,id:reply,comment:comment,action:'reply'};
	}
	api('post_comment',{
		data: data,
		reCAPTCHA:grecaptcha.getResponse(),
		callback: function(response){
			M.Toast.dismissAll();
			if (response == '3'){
				jQuery('#login-modal').modal('open');
			}
			else if (response == '2' || response == '4'){
				M.toast({html: 'An error occurred.'});
			}
			else if (response == '8'){
			    M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
			    document.getElementById("submit-comment-wrapper").innerHTML = '<button onclick="submit_comment()" class="waves-effect waves-light btn">Submit</button>';
			}
			else{
				M.toast({html: 'Your comment has been posted.'});
				jQuery('#comments-wrapper').html(response);
				jQuery('.dropdown-trigger').dropdown();
				recaptchaOnload();
			}
		}
	});
}
function delete_comment(id){
	document.getElementById("submit-comment-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
	var chapter_id = jQuery('#chapter_id').val();
	api('post_comment',{
		reCAPTCHA: grecaptcha.getResponse(),
		data: {chapter_id:chapter_id,id:id,action:'delete'},
		callback: function(response){
			M.Toast.dismissAll();
			if (response == '3'){
				jQuery('#login-modal').modal('open');
			}
			if (response == '2'){
				M.toast({html: 'An error occured.'});
			}
			else if (response == '8'){
			    M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
			    document.getElementById("submit-comment-wrapper").innerHTML = '<button onclick="submit_comment()" class="waves-effect waves-light btn">Submit</button>';
			}
			else{
				M.toast({html: 'Your comment has been deleted.'});
				jQuery('#comments-wrapper').html(response);
				jQuery('.dropdown-trigger').dropdown();
				recaptchaOnload();
			}
		}
	});
}