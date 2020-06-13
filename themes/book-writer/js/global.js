jQuery('.dropdown-trigger').dropdown();

jQuery('.modal:not(#searchbook)').modal();
jQuery('.scrollspy').scrollSpy();

jQuery('.tabs').tabs();
var links_tabs = jQuery('#nav-tabs a');
var active_exists = 0;
for (var i = 0; i < links_tabs.length; i++) {
	if(jQuery(links_tabs[i]).hasClass('pagenow') == true){
		active_exists = 1;
	}
}
if(active_exists == 0){
	jQuery('#nav-tabs .indicator').remove();
}
jQuery('.tooltipped').tooltip();
jQuery('.collapsible').collapsible();


function im(type){
	const selector = type === 'collections' ? 'article.collection-content' : 'article.mainsearch-item';
	const attr = type === 'collections' ? 'collection_id' : 'book_id';
	const height = window.innerHeight;
	const objs = jQuery(selector);
	let im__ = [];
	for (let i = 0; i < objs.length; i++) {
		const id = objs[i].getAttribute(attr);
		const co_ordinates = objs[i].getBoundingClientRect();
		const pos = (co_ordinates.top + co_ordinates.bottom)/2;
		if (pos >= 0 && pos <= height){
			im__.push(id);
		}
	}
	return im__;
}
let notification_open = false;
jQuery('#notification-sidenav').sidenav({edge:'right',onOpenEnd:function(){
	jQuery('.pulse.notification-badge')[0].innerHTML = '';	
	notification_open = true;
}});

let im_books = [];
let im_collections = [];
let lastSend = Date.now() - 20000;
jQuery(window).on('touchstart touchmove click wheel mousedown mouseup focus blur keydown change resize scroll',function(event){
	if (! event.originalEvent || event.originalEvent.isTrusted !== true){
		return;
	}
	im_books = array_unique(im_books.concat(im('books')));
	im_collections = array_unique(im_collections.concat(im('collections')));
	if (Date.now() - lastSend < 15000){
        return;
	}
	lastSend = Date.now();
	api('poll',{
		dataType: 'JSON',
		data: {
			data: document.getElementById('placeholder_data').innerHTML,
			im_books,
			im_collections,
			notification_open
		},
		callback: function(response){
			notification_open = false;
			let construct = '<li style="background-color:var(--light-theme-color);"><a class="subheader">Notifications</a></li>';
			for (let i = 0; i < response.notifications.notifications.length; i++) {
				const element = response.notifications.notifications[i];

				let icon_class = element.type === 'book_updated' ? 'fas fa-book' : null;
				icon_class = element.type === 'comment' ? 'fas fa-comment-alt' : icon_class;
				icon_class = element.type === 'message_received' ? 'fas fa-envelope' : icon_class;
				
				let desc = element.type === 'book_updated' ? `"${element.title}" from "${element.collection}" has been updated.`: null;
				desc = element.type === 'comment' ? `Someone commented on "${element.chapter_title}" from your book "${element.book_title}".` : desc;
				desc = element.type === 'message_received' ? `You\'ve received a message from ${element.from}.`: desc;

				construct += `<a target="_blank" style="display:block;" href="${element.link}"><li class="row btn-hover" style="cursor:pointer;margin-bottom:0;"><div class="center col s2 m1"><i class="${icon_class}"></i></div><div class="col s10 m11"><p style="padding-top:10px;line-height:22px;color:#9e9e9e !important;">${desc}</p><div style="
					color: #9e9e9e;
					line-height: 1;
					text-align: right;
					margin-bottom: 10px;
				">${timestamp_to_local(element.timestamp)}</div></div></li></a>`;
			}
			if (response.notifications.notifications.length === 0){
				construct += '<li><a class="subheader">No notifications.</a></li>';
			}
			jQuery('#notification-sidenav').children().remove();
			jQuery('#notification-sidenav').append(construct);
			if (jQuery('.pulse.notification-badge')[0]){
				jQuery('.pulse.notification-badge')[0].innerHTML = response.notifications.unread > 0 ? response.notifications.unread : '';
			}
		}
	});
});

////////Theme START ///////////////
//Parse Cookies
//Cookies Parsed: theme, line-height, font-size
var cookies_arr = document.cookie.split('; ');
const cookies = {};
for (let i = 0; i < cookies_arr.length; i++) {
    let split = cookies_arr[i].split('=');
    cookies[split[0]] = split[1];
}
//Set Cookies Function
function setCookie(name, value, options = { expires: 30, path: '/' }) {
  var d = new Date();
  d.setTime(d.getTime() + (options.expires*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = name + "=" + value + ";" + expires + ";path=" + options.path;
}

var current_theme = 'light';
function dark_mode(){
    current_theme = 'dark';
    var root = document.documentElement;
    root.style.setProperty("--theme-color", "#265f86");
    root.style.setProperty("--text-color", "#b7bfc4");
    root.style.setProperty("--background-color", "#121212");
    root.style.setProperty("--background-accent", "#212020");
    setCookie("theme","dark_mode", { expires:30, path: '/' });
}
function light_mode() {
    current_theme = 'light';
    var root = document.documentElement;
    root.style.setProperty("--theme-color", "#007ACC");
    root.style.setProperty("--text-color", "#262828");
    root.style.setProperty("--background-color", "#fdfdfd");
    root.style.setProperty("--background-accent", "#ececec");
    setCookie("theme","light_mode", { expires:30, path: '/' });
}
jQuery("#dark_mode").click(dark_mode);
jQuery("#light_mode").click(light_mode);
jQuery("#triggerTheme").click(function(){
    if (current_theme == 'dark'){
        light_mode();
    }
    else if (current_theme == 'light'){
        dark_mode();
    }
});
//Theme Settings
if (cookies["theme"] == "dark_mode"){dark_mode()}
else{light_mode()}
/////////////////Themes END /////////////////
