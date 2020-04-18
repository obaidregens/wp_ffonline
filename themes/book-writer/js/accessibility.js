////HTML is located in Chapter Page Template
////Script included in 'footer.php'

        
jQuery(document).ready
(
    function()
    {
			function theme_whiteonblack() 
			{
				jQuery(":root").get(0).style.setProperty("--theme-color", "#265f86");
				jQuery(":root").get(0).style.setProperty("--text-color", "white");
				jQuery(":root").get(0).style.setProperty("--background-color", "black");
				jQuery("#theme-whiteonblack").get(0).style.setProperty("display", "none");
				jQuery("#theme-pale").get(0).style.setProperty("display", "inline-block");
				jQuery("#theme-normal").get(0).style.setProperty("display", "inline-block");
					
				jQuery.cookie("theme","theme_whiteonblack", { expires:30, path: '/' });
			}
			function theme_normal() 
			{
				jQuery(":root").get(0).style.setProperty("--theme-color", "#007ACC");
				jQuery(":root").get(0).style.setProperty("--text-color", "#1a1a1a");
				jQuery(":root").get(0).style.setProperty("--background-color", "white");
				jQuery("#theme-normal").get(0).style.setProperty("display", "none");
				jQuery("#theme-pale").get(0).style.setProperty("display", "inline-block");
				jQuery("#theme-whiteonblack").get(0).style.setProperty("display", "inline-block");
				
				jQuery.cookie("theme","theme_normal", { expires:30, path: '/' });
			}	
			function theme_pale() 
			{
				jQuery(":root").get(0).style.setProperty("--theme-color", "#007ACC");
				jQuery(":root").get(0).style.setProperty("--text-color", "#1a1a1a");
				jQuery(":root").get(0).style.setProperty("--background-color", "#ECE1CB");
				jQuery("#theme-pale").get(0).style.setProperty("display", "none");
				jQuery("#theme-whiteonblack").get(0).style.setProperty("display", "inline-block");
				jQuery("#theme-normal").get(0).style.setProperty("display", "inline-block");
				
				jQuery.cookie("theme","theme_pale", { expires:30, path: '/' });
			}
			//fontSize default
			originalfontSize = parseInt(jQuery(".chapter-content").css("font-size"));
					
			//Line Height setting
			jQuery(".entry-content").get(0).style.setProperty("line-height",jQuery.cookie("line-height") + "px");
		
			//Font Size setting
			jQuery(".chapter-content").get(0).style.setProperty("font-size",jQuery.cookie("font-size") + "px");
			
			//Theme Settings
			if (jQuery.cookie("theme") == "theme_pale"){theme_pale()};
			if (jQuery.cookie("theme") == "theme_whiteonblack"){theme_whiteonblack()};
			if (jQuery.cookie("theme") == "theme_normal"){theme_normal()};
		
			//Container Settings
			if (jQuery.cookie("line-height")/jQuery.cookie("font-size") <= 1.2){jQuery("#decreaselineheight").get(0).style.setProperty("display", "none");}
			else if (jQuery.cookie("line-height")/jQuery.cookie("font-size") >= 3.5){jQuery("#increaselineheight").get(0).style.setProperty("display", "none");}
			if (jQuery.cookie("font-size") > 42){jQuery("#increasefontsize").get(0).style.setProperty("display", "none");}
			else if (jQuery.cookie("font-size") < 10){jQuery("#decreasefontsize").get(0).style.setProperty("display", "none");}
		

		
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
					jQuery.cookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
					jQuery.cookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });
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
					jQuery.cookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
					jQuery.cookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });				}
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
					jQuery.cookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
					jQuery.cookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });				}
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
					jQuery.cookie("font-size", parseInt(jQuery(".chapter-content").css("font-size")), { expires:30, path: '/' });
					jQuery.cookie("line-height", parseInt(jQuery(".entry-content").css("line-height")), { expires:30, path: '/' });				}
			)
			jQuery("#theme-whiteonblack").click(theme_whiteonblack);
			jQuery("#theme-normal").click(theme_normal);
			jQuery("#theme-pale").click(theme_pale);
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
//Focus Search on Open
jQuery('#searchbook').modal({onOpenEnd: function(){
    jQuery('#search_book').focus();
}});
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