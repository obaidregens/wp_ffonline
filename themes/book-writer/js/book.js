function save_collections(){
    jQuery('.progress').css('display','block');
    var switches = jQuery('input[id^=switch-]');
    var collections = [];
    for (let i = 0; i < switches.length; i++) {
        var collection_id = switches[i].id.replace('switch-','');
        if (switches[i].checked == true){
            collections.push(collection_id);
        }
    }
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/add_to_collection.php',
		type: 'post',
		data: {ajax:1,collections:collections,book_id:document.getElementById('book-id').innerHTML},
		success: function(response){
			jQuery('.progress').css('display','none');
		}
	});
}
function hide_book(id){
    jQuery('.progress').css('display','block');
	if (jQuery('i.icon-hide').hasClass('fa-eye') == true){
		jQuery('i.icon-hide').removeClass('fa-eye');
		jQuery('i.icon-hide').addClass('fa-eye-slash');
		jQuery('#d-switch-hidden').prop('checked',true);
	}
	else{
		jQuery('i.icon-hide').removeClass('fa-eye-slash');
		jQuery('i.icon-hide').addClass('fa-eye');
		jQuery('#d-switch-hidden').prop('checked',false);
	}
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/hide_book.php',
		type: 'post',
		data: {ajax:1,id:id},
		success: function(response){
			jQuery('.progress').css('display','none');
			if (response == 3){
			    M.toast({html: 'Please login.'});
			}
			else if (response == 0){
			    jQuery('#d-switch-hidden').prop('checked',false);
				jQuery('i.icon-hide').removeClass('fa-eye-slash');
				jQuery('i.icon-hide').addClass('fa-eye');
			}
			else if (response == 1){
			    jQuery('#d-switch-hidden').prop('checked',true);
				jQuery('i.icon-hide').removeClass('fa-eye');
				jQuery('i.icon-hide').addClass('fa-eye-slash');
			}
		}
	});
}
function favorite_this(id){
	jQuery('.favorite-wrapper')[0].innerHTML = '<div class="loader" style="width:21px;height:21px;"></div>';
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/favorite_this.php',
		type: 'post',
		data: {ajax:1,book_id:id},
		success: function(response){
			jQuery('.favorite-wrapper')[0].innerHTML = '<i onclick="favorite_this(' + id + ')" class="btn-favorite far fa-heart"></i>';
			if (response == 0){
				jQuery('.btn-favorite').removeClass('active');
				jQuery('#d-switch-favorite').prop('checked',false);
			}
			else if (response == 1){
				jQuery('.btn-favorite').addClass('active');
				jQuery('#d-switch-favorite').prop('checked',true);
			}
			else if (response == 3){
				jQuery('#login-modal').modal('open');
			}
		}
	});
}
jQuery('.scrollspy').scrollSpy();