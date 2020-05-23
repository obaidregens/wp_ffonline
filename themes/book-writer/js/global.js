var messages_count;
jQuery(document).ready(function(){
	jQuery('.dropdown-trigger').dropdown();
	jQuery('#notification-sidenav').sidenav({edge:'right',onCloseEnd:function(){
	    if (jQuery('.pulse.notification-badge')[0].innerHTML != ''){
    	    messages_count += parseInt(jQuery('.pulse.notification-badge')[0].innerHTML);
    	    jQuery('.pulse.notification-badge')[0].innerHTML = '';	        
        }
	}});
    jQuery('.modal:not(#searchbook)').modal();
    
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
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/message_notifications.php',
		type: 'post',
		data: {ajax: 1,data: 'count'},
		success: function(response){
		    messages_count = parseInt(response);
		}
	});
});
function new_message_notification() {
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/message_notifications.php',
		type: 'post',
		data: {ajax: 1,data:'count',read:messages_count},
		success: function(response){
			if (response > messages_count){
			    var diff = response - messages_count;
				if (diff != parseInt(jQuery('.pulse.notification-badge')[0].innerHTML)){
                    jQuery('.pulse.notification-badge')[0].innerHTML = diff;
                	jQuery.ajax({
                		url: '/wp-content/themes/book-writer/php/message_notifications.php',
                		type: 'post',
                		data: {ajax: 1,data: 'full'},
                		success: function(response){
                		    jQuery('#notification-sidenav')[0].innerHTML = response;
                		}
                	});				    
				}
			}
		}
	});     
}

var notifyMessageID = setInterval(new_message_notification, 10000);


////////Theme START ///////////////
//Parse Cookies
//Cookies Parsed: theme, line-height, font-size
var cookies_arr = document.cookie.split('; ');
var cookies = {};
for (let i = 0; i < cookies_arr.length; i++) {
    let split = cookies_arr[i].split('=');
    cookies[split[0]] = split[1];
}
//Set Cookies Function
function setCookie(name, value, options) {
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



/////////preg_split function used in PHP
function preg_split (pattern, subject, limit, flags) {
    // http://kevin.vanzonneveld.net
    // + original by: Marco Marchi??
    // * example 1: preg_split(/[\s,]+/, 'hypertext language, programming');
    // * returns 1: ['hypertext', 'language', 'programming']
    // * example 2: preg_split('//', 'string', -1, 'PREG_SPLIT_NO_EMPTY');
    // * returns 2: ['s', 't', 'r', 'i', 'n', 'g']
    // * example 3: var str = 'hypertext language programming';
    // * example 3: preg_split('/ /', str, -1, 'PREG_SPLIT_OFFSET_CAPTURE');
    // * returns 3: [['hypertext', 0], ['language', 10], ['programming', 19]]
    // * example 4: preg_split('/( )/', '1 2 3 4 5 6 7 8', 4, 'PREG_SPLIT_DELIM_CAPTURE');
    // * returns 4: ['1', ' ', '2', ' ', '3', ' ', '4 5 6 7 8']
    // * example 5: preg_split('/( )/', '1 2 3 4 5 6 7 8', 4, (2 | 4));
    // * returns 5: [['1', 0], [' ', 1], ['2', 2], [' ', 3], ['3', 4], [' ', 5], ['4 5 6 7 8', 6]]

    limit = limit || 0; flags = flags || ''; // Limit and flags are optional

    var result, ret=[], index=0, i = 0,
        noEmpty = false, delim = false, offset = false,
        OPTS = {}, optTemp = 0,
        regexpBody = /^\/(.*)\/\w*$/.exec(pattern.toString())[1],
        regexpFlags = /^\/.*\/(\w*)$/.exec(pattern.toString())[1];
        // Non-global regexp causes an infinite loop when executing the while,
        // so if it's not global, copy the regexp and add the "g" modifier.
        pattern = pattern.global && typeof pattern !== 'string' ? pattern :
            new RegExp(regexpBody, regexpFlags+(regexpFlags.indexOf('g') !==-1 ? '' :'g'));

    OPTS = {
        'PREG_SPLIT_NO_EMPTY': 1,
        'PREG_SPLIT_DELIM_CAPTURE': 2,
        'PREG_SPLIT_OFFSET_CAPTURE': 4
    };
    if (typeof flags !== 'number') { // Allow for a single string or an array of string flags
        flags = [].concat(flags);
        for (i=0; i < flags.length; i++) {
            // Resolve string input to bitwise e.g. 'PREG_SPLIT_OFFSET_CAPTURE' becomes 4
            if (OPTS[flags[i]]) {
                optTemp = optTemp | OPTS[flags[i]];
            }
        }
        flags = optTemp;
    }
    noEmpty = flags & OPTS.PREG_SPLIT_NO_EMPTY;
    delim = flags & OPTS.PREG_SPLIT_DELIM_CAPTURE;
    offset = flags & OPTS.PREG_SPLIT_OFFSET_CAPTURE;

    var _filter = function(str, strindex) {
        // If the match is empty and the PREG_SPLIT_NO_EMPTY flag is set don't add it
        if (noEmpty && !str.length) {return;}
        // If the PREG_SPLIT_OFFSET_CAPTURE flag is set
        //      transform the match into an array and add the index at position 1
        if (offset) {str = [str, strindex];}
        ret.push(str);
    };
    // Special case for empty regexp
    if (!regexpBody){
        result=subject.split('');
        for (i=0; i < result.length; i++) {
            _filter(result[i], i);
        }
        return ret;
    }
    // Exec the pattern and get the result
    while (result = pattern.exec(subject)) {
        // Stop if the limit is 1
        if (limit === 1) {break;}
        // Take the correct portion of the string and filter the match
        _filter(subject.slice(index, result.index), index);
        index = result.index+result[0].length;
        // If the PREG_SPLIT_DELIM_CAPTURE flag is set, every capture match must be included in the results array
        if (delim) {
            // Convert the regexp result into a normal array
            var resarr = Array.prototype.slice.call(result);
            for (i = 1; i < resarr.length; i++) {
                if (result[i] !== undefined) {
                    _filter(result[i], result.index+result[0].indexOf(result[i]));
                }
            }
        }
        limit--;
    }
    // Filter last match
    _filter(subject.slice(index, subject.length), index);
    return ret;
}