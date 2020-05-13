jQuery(document).ready(function(){
    jQuery('#dashboard-sidenav').sidenav();
    var urlParams = new URLSearchParams(location.search);
    var page_chain = window.location.href.replace(window.location.origin + '/dashboard','').replace('/','');
    load_page(page_chain);
});
function load_page(page_chain){
    var page_chain_arr = page_chain.split('/').filter(function(elem){
        if (elem != ""){
            return elem;
        }
    });
    var pages = ['write','profile','stats','messages','chat','settings','bookmarks','collections'];
    if (! pages.includes(page_chain_arr[0])){
        return;
    }
    page_chain = page_chain_arr.join('/');
    jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');
    if(jQuery('[data-target=dashboard-sidenav]').css('display') != 'none'){
		jQuery('#dashboard-sidenav').sidenav('close');
    }
    window.history.pushState("object or string", document.getElementsByTagName("title")[0].innerHTML,'/dashboard/' + page_chain);
    clearInterval(intervalID);
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/load_page.php',
		type: 'post',
		data: {ajax:1,page_chain},
		success: function(response){
            jQuery("#page-main").html(response);
            jQuery('#dashboard-sidenav').children().removeClass("active");
			jQuery("." + page_chain_arr[0] + "_li").addClass("active");
			jQuery('.collapsible').collapsible();
            jQuery('.modal').modal();
			M.updateTextFields();
			recaptchaOnload();
		}
	});
}