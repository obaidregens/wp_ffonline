jQuery(document).ready(function(){jQuery('#dashboard-sidenav').sidenav();jQuery("html").css("cssText",'margin-top:0px !important;');var urlParams=new URLSearchParams(location.search);var pages=['write','profile','stats','edit-chapter','messages','chat','settings','edit-book','bookmarks','collections'];if(urlParams.get('to')!==null){if(pages.includes(urlParams.get('to'))){if(urlParams.get('to')=='edit-chapter'){load_page(urlParams.get('to'),urlParams.get('chap'));}
else if(urlParams.get('to')=='edit-book'){load_page(urlParams.get('to'),urlParams.get('edit-book'));}
else if(urlParams.get('to')=='chat'){load_page(urlParams.get('to'),urlParams.get('id'));}
else{load_page(urlParams.get('to'));}}}});function load_page(page,extra=null){if(page!='collections'){M.Toast.dismissAll();}
if(typeof intervalID!=='undefined'){clearInterval(intervalID);}
jQuery("#page-main").html('<main><div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div></main>');if(jQuery('[data-target=dashboard-sidenav]').css('display')!='none'){jQuery('#dashboard-sidenav').sidenav('close');}
var data={ajax:1,page:page};if(extra!==null){if(page=='edit-chapter'){data['chap']=extra;}
else if(page=='chat'){data['id']=extra;}
else if(page=='edit-book'){data['edit-book']=extra;}}
jQuery.ajax({url:'/wp-content/themes/book-writer/php/load_page.php',type:'post',data:data,success:function(response){jQuery("#page-main").html(response);var children=jQuery('#dashboard-sidenav').children();for(i=0;i<children.length;i++){jQuery(children[i]).removeClass("selected");}
jQuery("."+page+"_li").addClass("selected");jQuery('.collapsible').collapsible();M.updateTextFields();recaptchaOnload();}});};