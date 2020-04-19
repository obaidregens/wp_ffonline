jQuery("form[id='update_form']").submit(function(event) {
	event.preventDefault();
	jQuery("#edit-update .modal-footer")[0].innerHTML = '<button type="submit" class="waves-effect btn">Save</button>';
	var update_id = document.getElementById('update_id').value;
	var update = document.getElementById('update').value;
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/edit_update.php',
		type: 'post',
		data: {ajax: 1,update_id:update_id,update:update},
		success: function(response){
			jQuery("#edit-update .modal-footer")[0].innerHTML = '<button type="submit" class="waves-effect btn">Save</button>';
			M.Toast.dismissAll();
			if (response == 2){
				M.toast({html: 'Update added.'});
				window.location.reload();
			}
			else if (response == 4){
				M.toast({html: 'Update edited.'});
				window.location.reload();
			}
			else if (response == 3){
				jQuery('#login-modal').modal('open');
			}
			else if (response == 6){
				M.toast({html: 'An error occured.'});
			}
		}
	});
});
jQuery('.new-update').click(function(event){
	document.getElementById('update_id').value = 'new';
	jQuery('#edit-update').modal('open');
	jQuery('#update').val('');
	M.updateTextFields();
});
jQuery('.edit-update').click(function(event){
	var id = this.getAttribute('update_id');
	var update_c = jQuery('article[id=update-' + id + '] pre').html();
	document.getElementById('update_id').value = id;
	jQuery('#edit-update').modal('open');
	jQuery('#update').val(update_c);
	M.updateTextFields();
});
jQuery('.delete-update,.stick-update').click(function(event){
	var id = this.getAttribute('update_id');
	var action = this.getAttribute('class').replace('-update','');
	jQuery('.progress').css('display','block');
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/edit_update.php',
		type: 'post',
		data: {ajax: 1,update_id:id,action:action},
		success: function(response){
			jQuery('.progress').css('display','none');
			M.Toast.dismissAll();
			if (response == 1){
				//New Email
				M.toast({html: 'Update deleted.'});
				window.location.reload();
			}
			else if (response == 11){
				M.toast({html: 'Update unpinned.'});
				window.location.reload();
			}
			else if (response == 12){
				M.toast({html: 'Update pinned.'});
				window.location.reload();
			}
			else if (response == 3){
				jQuery('#login-modal').modal('open');
			}
			else if (response == 6){
				M.toast({html: 'An error occured.'});
			}
		}
	});
});