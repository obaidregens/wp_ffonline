jQuery(document).ready(function(){
    jQuery('#dashboard-sidenav').sidenav();
    var urlParams = new URLSearchParams(location.search);
    var pages = ['write','profile','stats','edit-chapter','messages','chat','settings','edit-book','bookmarks','collections'];
    var page_full = window.location.href.replace(window.location.origin + '/dashboard','').replace('/','').split('/');
    var page_to = page_full[0].replace(/\//g,'');
    var page_id = '';
    if (page_full[1]){
        page_id = page_full[1].replace(/\//g,'');
    }
    if (page_to != ''){
        if(pages.includes(page_to)){
            if ((page_to == 'edit-chapter' || page_to == 'edit-book' || page_to == 'chat') && page_id != ''){
                load_page(page_to,page_id);
            }
            else{
               load_page(page_to);
            }
        }
    }    
});
function load_page(page,extra = null){
    if (page != 'collections'){
        M.Toast.dismissAll();
    }
    if (typeof intervalID !== 'undefined') {
        clearInterval(intervalID);
    }
    
    jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');
    if(jQuery('[data-target=dashboard-sidenav]').css('display') != 'none'){
		jQuery('#dashboard-sidenav').sidenav('close');
	}
    var construct = '/dashboard/' + page;
	var data = {ajax: 1,page:page};
	if (extra !== null){
        data['id'] = extra;
        construct += '/' + extra;
	}
    if (page == 'dashboard'){
        construct = '/dashboard';
    }
    window.history.pushState("object or string", document.getElementsByTagName("title")[0].innerHTML,construct);
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/load_page.php',
		type: 'post',
		data: data,
		success: function(response){
            jQuery("#page-main").html(response);
            var children = jQuery('#dashboard-sidenav').children();
            for (var i = 0; i < children.length; i++) {
                jQuery(children[i]).removeClass("selected");
            }
			jQuery("." + page + "_li").addClass("selected");
			jQuery('.collapsible').collapsible();
            jQuery('.modal').modal();
			M.updateTextFields();
			recaptchaOnload();
		}
	});
}