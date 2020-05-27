jQuery('body').on("click",'.book-share',function(){
	const book_elem = jQuery(this).parents('article.mainsearch-item')[0];
	const title = jQuery(book_elem).find('.entry-title a')[0].innerHTML;
	const desc = jQuery(book_elem).find('.search-summary p')[0].innerHTML;
	const href = jQuery(book_elem).find('.entry-title a')[0].href;
	const author = jQuery(book_elem).find('.entry-author a')[0].innerHTML;

	const share_obj = {
		title: 'I found a great fanfiction by ' + author + ' \'' + title + '\'!',
		url: href,
		text: desc
	}
	if (navigator.share) {
	navigator.share(share_obj).then(() => {
		//
	})
	.catch(console.error);
	} else {
	jQuery('html').append('<div id="share_modal" class="modal"><div class="modal-content"><h1 class="share_title">Share</h1><div class="book_box"><h4 class="book_title_author"></h4><p class="book_description"></p></div><div class="share_icons"><a target="_blank" class="btn-hover btn-floating whatsapp_share"><i class="fab fa-whatsapp"></i></a><a target="_blank" class="btn-hover btn-floating twitter_share"><i class="fab fa-twitter"></i></a><a target="_blank" class="btn-hover btn-floating reddit_share"><i class="fab fa-reddit-alien"></i></a><a target="_blank" class="btn-hover btn-floating facebook_share"><i class="fab fa-facebook-f"></i></a><a class="btn-hover btn-floating copy_share"><i class="fas fa-link"></i></a></div></div></div>');
	jQuery('#share_modal').modal({onCloseEnd:function(){
		jQuery('#share_modal').remove();
	}});
	jQuery('#share_modal').modal('open');
	jQuery('#share_modal .book_title_author').html(share_obj.title);
	jQuery('#share_modal .book_description').html(share_obj.text);

	const text_s = share_obj.title + '%0A' + share_obj.text;
	jQuery('.whatsapp_share')[0].href = 'https://api.whatsapp.com/send?text=' + text_s + '%0A%0ARead it now: ' + share_obj.url;
	jQuery('.twitter_share')[0].href = 'https://twitter.com/intent/tweet?url=' + share_obj.url + '&text=' + text_s;
	jQuery('.reddit_share')[0].href = 'https://www.reddit.com/submit?url=' + share_obj.url + '&title=' + text_s;
	jQuery('.facebook_share')[0].href = 'https://www.facebook.com/sharer/sharer.php?u=' + share_obj.url + '&quote=' + text_s;
	
	jQuery('.copy_share')[0].setAttribute('href_copy',share_obj.url);
	jQuery('.copy_share').click(function(){
		const href = this.getAttribute("href_copy");
		jQuery('html').append('<span id="copy_bubble">' + href + '</span>');
		selectText('copy_bubble');
		document.execCommand("copy");
		jQuery('#copy_bubble').remove();
		M.toast({html: 'Copied!'});
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


const book_collections_elem = document.getElementById('book_collections');
let book_collections = {};
if (book_collections_elem){
	book_collections = JSON.parse(book_collections_elem.innerHTML);
}
let all_articles = jQuery('article.mainsearch-item');
for (let i = 0; i < all_articles.length; i++) {
	let id = all_articles[i].id.replace('book-','');
	if (! book_collections[id] ){
		book_collections[id] = [];
	}
	renderBookCollections(id);
}

//Render
function renderBookCollections(id){

	//Dropdown Render
	const article = jQuery('article.mainsearch-item#book-' + id)[0];
	jQuery(article).find('.book-favorite')[0].innerHTML =
		book_collections[id].includes('favorites') ? '<i class="fas fa-heart"></i>Remove' : '<i class="fas fa-heart"></i>Favorite';
	jQuery(article).find('.book-hide')[0].innerHTML = 
		book_collections[id].includes('hidden') ? '<i class="fas fa-eye-slash"></i>Show' : '<i class="fas fa-eye-slash"></i>Hide';

	//Collection Render
	const collection_modal = jQuery('.collections-modal[book_id="' + id + '"]')[0];
	if (! collection_modal){
		return;
	}

	//Uncheck all first
	const all_collection_switches = jQuery(collection_modal).find('input.save-collection');
	for (let i = 0; i < all_collection_switches.length; i++) {
		let elem = all_collection_switches[i];
		jQuery(elem).prop('checked',false);
	}
	//Check included
	for (let i = 0; i < book_collections[id].length; i++) {
		let collection_id = book_collections[id][i];
		jQuery(collection_modal).find('[collection_id="' + collection_id +'"]').prop('checked',true);
	}
}
//This will open Collections Modal
jQuery('body').on("click",'.book-collections',function(){
	if (this.hasAttribute("disabled")){
		jQuery('#login-modal').modal('open');
	}
	else{
		var id = jQuery(this).parents('article.mainsearch-item')[0].id.replace('book-','');
		var collection_modal = jQuery('.collections-modal')[0];
		collection_modal.setAttribute('book_id',id);

		renderBookCollections(id);

		M.Modal.getInstance(collection_modal).open();
	}
});

//This is for the Favorite and Hidden buttons in the dropdown
jQuery('body').on("click",'.book-favorite, .book-hide',function(){
	let collection_id = '';
	if (jQuery(this).hasClass('book-favorite')){
		collection_id = 'favorites';
	}
	else if (jQuery(this).hasClass('book-hide')){
		collection_id = 'hidden';
	}
	else{
		return;
	}
	jQuery('.progress').css('display','block');
	
	const article = jQuery(this).parents('article.mainsearch-item')[0];
	const id = article.id.replace('book-','');

	jQuery(article).find('.book-collections').click();
	
	const collection_modal = jQuery('.collections-modal')[0];
	M.Modal.getInstance(collection_modal).close();

	jQuery(collection_modal).find('[collection_id="' + collection_id +'"]').click();


});

//Click on switch in collection modal
jQuery('body').on("click",'.save-collection',function(){
	jQuery('.progress').css('display','block');

	const collection_modal = jQuery(this).parents('.collections-modal');
	const id = collection_modal[0].getAttribute('book_id');

	const switches = jQuery(collection_modal).find('.save-collection');
	let collections = [];
	for (let i = 0; i < switches.length; i++) {
		let collection_id = switches[i].getAttribute("collection_id");
		if (switches[i].checked == true){
			collections.push(collection_id);
		}
	}
	book_collections[id] = collections;

	renderBookCollections(id);
	const data_submit = {
		ajax: 1,
		book_collections: {}
	}
	data_submit.book_collections[id] = collections;
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/add_to_collection.php',
		type: 'post',
		dataType: 'JSON',
		data: {data_: JSON.stringify(data_submit)},
		success: function(response){
			if (response.code == 6){
				jQuery('#login-modal').modal('open');
			}
			else if (response.code > 5){
				M.toast({html: 'An error occured.'});
			}
			jQuery('.progress').css('display','none');
		}
	});
});