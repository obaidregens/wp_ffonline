const book_collections_elem = document.getElementById('book_collections');
let book_collections = {};
if (book_collections_elem){
	book_collections = JSON.parse(book_collections_elem.innerHTML);
}

jQuery(document).ready(function(){
	jQuery('body').on("click",'.book-share',function(){
		var book_elem = jQuery(this).parents('article.mainsearch-item')[0];
		var title = jQuery(book_elem).find('.entry-title a')[0].innerHTML;
		var desc = jQuery(book_elem).find('.search-summary p')[0].innerHTML;
		var href = jQuery(book_elem).find('.entry-title a')[0].href;
		var author = jQuery(book_elem).find('.entry-author a')[0].innerHTML;
	  if (navigator.share) {
	    navigator.share({
	      title: title + ' - by ' + author,
	      url: href
	    }).then(() => {
	      //
	    })
	    .catch(console.error);
	  } else {
		jQuery('html').append('<div id="share_modal" class="modal"><div class="modal-content"><h1 class="share_title">Share</h1><div class="book_box"><h4 class="book_title_author"></h4><p class="book_description"></p></div><div class="share_icons"><a target="_blank" class="btn-hover btn-floating whatsapp_share"><i class="fab fa-whatsapp"></i></a><a target="_blank" class="btn-hover btn-floating twitter_share"><i class="fab fa-twitter"></i></a><a target="_blank" class="btn-hover btn-floating reddit_share"><i class="fab fa-reddit-alien"></i></a><a target="_blank" class="btn-hover btn-floating facebook_share"><i class="fab fa-facebook-f"></i></a><a class="btn-hover btn-floating copy_share"><i class="fas fa-link"></i></a></div></div></div>');
	    jQuery('#share_modal').modal({onCloseEnd:function(){
	    	jQuery('#share_modal').remove();
	    }});
	    jQuery('#share_modal').modal('open');
	    jQuery('#share_modal .book_title_author').html(title + ' - by ' + author);
	    jQuery('#share_modal .book_description').html(desc);

	    var text_s = 'I found a great fanfiction by ' + author + ' \'' + title + '\'!%0A' + desc;
	    jQuery('.whatsapp_share')[0].href = 'https://api.whatsapp.com/send?text=' + text_s + '%0A%0ARead it now: ' + href;
	    jQuery('.twitter_share')[0].href = 'https://twitter.com/intent/tweet?url=' + href + '&text=' + text_s;
	    jQuery('.reddit_share')[0].href = 'https://www.reddit.com/submit?url=' + href + '&title=' + text_s;
	    jQuery('.facebook_share')[0].href = 'https://www.facebook.com/sharer/sharer.php?u=' + href + '&quote=' + text_s;
	    jQuery('.copy_share')[0].setAttribute('href_copy',href);
		jQuery('.copy_share').click(function(){
			var href = this.getAttribute("href_copy");
			jQuery('html').append('<span id="copy_bubble">' + href + '</span>');
			selectText('copy_bubble');
			document.execCommand("copy");
			jQuery('#copy_bubble').remove();
			M.toast({html: 'Copied!'})
		});
	  }
	});
	function selectText(node) {
	    node = document.getElementById(node);

	    if (document.body.createTextRange) {
	        const range = document.body.createTextRange();
	        range.moveToElementText(node);
	        range.select();
	    } else if (window.getSelection) {
	        const selection = window.getSelection();
	        const range = document.createRange();
	        range.selectNodeContents(node);
	        selection.removeAllRanges();
	        selection.addRange(range);
	    } else {
	        console.warn("Could not select text in node: Unsupported browser.");
	    }
	}
	jQuery('body').on("click",'.book-hide',function(){
		jQuery('.progress').css('display','block');
		var collection_modal = jQuery(this).parents('.collections-modal');
		if (collection_modal){
			var id = collection_modal.attr('book_id');
		}
		else{
			var id = jQuery(this).parents('article.mainsearch-item')[0].id.replace('book-','');
		}
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/hide_book.php',
			type: 'post',
			data: {ajax:1,id:id},
			success: function(response){
				console.log(response);
				jQuery('.progress').css('display','none');
				var li_elem = jQuery('#book-options-' + id).find('.book-hide')[0];
				var switch_elem = jQuery('[book_id="' + id + '"].collections-modal').find('.book-hide')[0];
				if (response == 3){
				    jQuery('#login-modal').modal('open');	
				}
				else if (response == 0){
					li_elem.innerHTML = '<i class="fas fa-eye-slash"></i>';
					li_elem.innerHTML += 'Hide';
					switch_elem.checked = false;
				}
				else if (response == 1){
					li_elem.innerHTML = '<i class="fas fa-eye-slash"></i>';
					li_elem.innerHTML += 'Show';
					switch_elem.checked = true;
				}
				else if (response == 6){}
			}
		});
	});

	jQuery('body').on("click",'.book-collections',function(){
		if (this.hasAttribute("disabled")){
			jQuery('#login-modal').modal('open');
		}
		else{
			var id = jQuery(this).parents('article.mainsearch-item')[0].id.replace('book-','');
			var collection_modal = document.querySelector('.collections-modal');
			collection_modal.setAttribute('book_id',id);
			if (! book_collections[id]){
				book_collections[id] = [];
			}

			for (let i = 0; i < book_collections[id].length; i++) {
				let collection_id = book_collections[id][i];
				jQuery(collection_modal).find('[collection_id="' + collection_id +'"]').prop('checked',true);
			}
			M.Modal.getInstance(collection_modal).open();
		}
	});
	jQuery('body').on("click",'.book-favorite',function(){
		
		jQuery('.progress').css('display','block');
		var collection_modal = jQuery(this).parents('.collections-modal');
		if (collection_modal){
			var id = collection_modal.attr('book_id');
		}
		else{
			var id = jQuery(this).parents('article.mainsearch-item')[0].id.replace('book-','');
		}
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/favorite_this.php',
			type: 'post',
			data: {ajax:1,book_id:id},
			success: function(response){
				jQuery('.progress').css('display','none');
				var li_elem = jQuery('#book-options-' + id).find('.book-favorite')[0];
				var switch_elem = jQuery('[book_id="' + id + '"].collections-modal').find('.book-favorite')[0];
				if (response == 3){
				    jQuery('#login-modal').modal('open');
				}
				else if (response == 0){
					li_elem.innerHTML = '<i class="fas fa-heart"></i>';
					li_elem.innerHTML += 'Favorite';
					switch_elem.checked = false;
				}
				else if (response == 1){
					li_elem.innerHTML = '<i class="fas fa-heart"></i>';
					li_elem.innerHTML += 'Remove';
					switch_elem.checked = true;
				}
				else if (response == 6){}
			}
		});
	});

	jQuery('body').on("click",'.save-collection',function(){

		jQuery('.progress').css('display','block');
		var collection_modal = jQuery(this).parents('.collections-modal')[0];
        var id = collection_modal.getAttribute('book_id');
        var switches = jQuery(collection_modal).find('.save-collection');
        var collections = [];
        for (i = 0; i < switches.length; i++) {
            var collection_id = switches[i].getAttribute("collection_id");
            if (switches[i].checked == true){
                collections.push(collection_id);
            }
        }
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/add_to_collection.php',
			type: 'post',
			data: {ajax:1,collections:collections,book_id:id},
			success: function(response){
				if (response == 3){
				    jQuery('#login-modal').modal('open');
				}
				if (response == 6){	}
				jQuery('.progress').css('display','none');
			}
		});
	});
})