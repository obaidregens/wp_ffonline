//HTML is located in Chapter Page Template
//Script included in 'footer.php'

function acs(action){
	const root = document.documentElement;
	if (action === 'set'){
		const acs_vars = ['font-size','margin','line-height','p-height'];
		let acs_cookie = '';
		for (let i = 0; i < acs_vars.length; i++) {
			acs_cookie += acs_vars[i] + '>' + parseInt(getComputedStyle(root).getPropertyValue('--acs-' + acs_vars[i])) + '/';
		}
		acs_cookie += 'auto_scroller>' + (jQuery('#autoscroll')[0].checked === true ? (parseInt( jQuery('#autoscroll-s').val() ) || 0) : 0) + '/';
		acs_cookie += 'shade>' + getComputedStyle(root).getPropertyValue('--acs-shade') + '/';
		acs_cookie += 'font>' + getComputedStyle(root).getPropertyValue('--acs-font') + '/';
		setCookie("acs",acs_cookie, { expires:30, path: '/' });
	}
	else if (action === 'get'){
		if (! cookies.acs){
			return {};
		}
		const acs = cookies.acs.split('/');
		acs.pop()
		const acs_styles = {};
		for (let i = 0; i < acs.length; i++) {
			let split = acs[i].split('>');
			acs_styles[split[0]] = split[1];
		}
		return acs_styles;
	}
}
function css_var({which,adder,min,max}){
	const new_value = parseInt(getComputedStyle(document.documentElement).getPropertyValue(which)) + adder;
	if (new_value < min || new_value > max){
		return;
	}
	document.documentElement.style.setProperty(which,new_value);
}
jQuery('.acs-btn').click(function(){
	const options = {};
	const action = this.getAttribute('action');
	const inc = this.getAttribute('inc');
	if (action === 'font-size'){
		options.which = '--acs-font-size';
		options.min = 10;
		options.max = 30;
	}
	else if (action === 'line-height'){
		options.which = '--acs-line-height';
		options.min = 5;
		options.max = 15;
	}
	else if (action === 'p-height'){
		options.which = '--acs-p-height';
		options.min = 0;
		options.max = 10;
	}
	else if (action === 'margin'){
		options.which = '--acs-margin';
		options.min = 0;
		options.max = 30;
	}
	else if (action === 'shade'){
		const root = document.documentElement;
		root.style.setProperty("--theme-color", "#007ACC");
		root.style.setProperty("--text-color", "#262828");
		root.style.setProperty("--background-color", "#fdfdfd");
		root.style.setProperty("--background-accent", "#ececec");
		root.style.setProperty('--acs-shade',getComputedStyle(this).backgroundColor);
	}
	else if (action === 'font'){
		const root = document.documentElement;
		root.style.setProperty('--acs-font',this.style.getPropertyValue('font-family'));
	}
	if (inc === '+'){
		options.adder = 1;
	}
	else if (inc === '-'){
		options.adder = -1;
	}
	css_var(options);
	acs('set');
});
const _acs = acs('get');
const _acs_keys = Object.keys(_acs);
for (let i = 0; i < _acs_keys.length; i++) {
	const key_ = _acs_keys[i];
	document.documentElement.style.setProperty('--acs-' + key_,_acs[key_]);
}
if (_acs.shade === 'rgb(237, 209, 176)'){
	jQuery('.acs-btn[action="shade"][color="peach"]').click();
}
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
	const sel = window.getSelection();
	if (sel.anchorNode !== null && (jQuery(sel.anchorNode.parentElement).parents('#define').length != 0 || sel.anchorNode.parentElement.id == 'define')){
	}
	else if (
		sel.anchorNode === null ||
		sel.toString() == '' ||
		jQuery(sel.anchorNode.parentElement).parents('.acs-main').length == 0
	){
		jQuery('#define').css('display','none');
		jQuery('#define-full').modal('close');
	}
	else{
		var define = sel.toString().split(' ')[0];
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
//jQuery(".acs-main").mouseup(define_text);
//jQuery(".acs-main").change(define_text);
//jQuery(".acs-main").click(define_text);
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
//Auto Scroller
jQuery('#autoscroll').change(function(){
	const is_chec = jQuery('#autoscroll')[0].checked;
	jQuery('#autoscroll-s-wrapper').css('display',is_chec === true ? 'inline-block' : 'none');
	const falsey_scroll = jQuery('#autoscroll-s').val() === '' || jQuery('#autoscroll-s').val() === '0';
	if (is_chec === true && falsey_scroll === true){
		jQuery('#autoscroll-s').val(40);
	}
});

let auto_scroll_interval = -1;
jQuery('#autoscroll')[0].checked = true;
jQuery('#autoscroll').change();
jQuery('#autoscroll-s').val(acs('get').auto_scroller || 0);
accessibility_close();

jQuery('#accessibility').modal({onCloseEnd: accessibility_close});
function accessibility_close(){
	jQuery('.stop_autoscroll').remove();
	clearInterval(auto_scroll_interval);
	acs('set');
	if (jQuery('#autoscroll')[0].checked === false){
		return;
	}
	$seconds = parseInt( jQuery('#autoscroll-s').val() ) || 0;
	if ($seconds === 0){
		jQuery('#autoscroll')[0].checked = false;
		jQuery('#autoscroll').change();
		return;
	}
	jQuery('#autoscroll-s').val($seconds);
	jQuery('html').append(`<button style="
			z-index: 997;
			position: fixed;
			top: 9px;
			transform: scale(0.8);
			right: 32px;
		" class="btn-small stop_autoscroll waves-effect waves-light btn">AutoScroll Off</button>`);
	jQuery('.stop_autoscroll').click(function(){
		jQuery('#accessibility').modal('open');
		jQuery('#autoscroll')[0].checked = false;
		jQuery('#autoscroll').change();
		jQuery('#accessibility').modal('close');
	});
	set_autoscroll($seconds);
}
function set_autoscroll($seconds){
	auto_scroll_interval = setInterval(function(){
		const win = document.documentElement;
		const skip = win.clientHeight * 0.85;
		const new_height = win.scrollTop + skip;

		jQuery('html').animate({
			scrollTop: new_height
		}, 1500);

		if ( jQuery('.acs-main')[0].getBoundingClientRect().bottom <= win.clientHeight ){
			// jQuery('#autoscroll')[0].checked = false;
			// clearInterval(auto_scroll_interval);
		}
	},$seconds * 1000);
}
//Theme
jQuery('#theme_switch')[0].checked = current_theme === 'light' ? false : true ;
jQuery('#triggerTheme').click(function(){
	jQuery('#theme_switch')[0].checked = ! jQuery('#theme_switch')[0].checked;
})
jQuery('#theme_switch').change(function(){
	this.checked === true ? dark_mode() : light_mode()
});