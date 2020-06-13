<?php
$current_user = wp_get_current_user(  );
$new_email = get_user_meta( get_current_user_id(), '_new_email', true );
$perm_email_help = '';
if ( $new_email && $new_email['newemail'] != $current_user->user_email){
	$perm_email_help = 'Please check your inbox at <strong>' . $new_email['newemail'] . '</strong> to confirm your new email.';
}
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<form name="editprofile" class="col s12 mobile-margin">
			<div class="row">
				<div class="input-field col s12">
					<textarea name="about" id="about" class="materialize-textarea"><?php echo get_the_author_meta('user_description',get_current_user_id()); ?></textarea>
					<label for="about">About</label>
					<span class="helper-text">Something for your readers to know about you.</span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<input type="text" disabled value="<?php echo get_the_author_meta('user_login',get_current_user_id()); ?>">
					<label>Username</label>
					<span class="helper-text"><a style="cursor: pointer;" onclick="M.Modal.getInstance(document.getElementById('username_change_modal')).open();">Change Username</a></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<input name="email" id="email" type="email" class="validate" required value="<?php echo get_the_author_meta('user_email',get_current_user_id()); ?>">
					<label for="email">Email</label>
					<span id="email_help" class="helper-text"><?= $perm_email_help; ?></span>
					<span id="perm_email_help" style="display:none"><?= $perm_email_help; ?></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					  <input autocomplete="new-password" name="password" id="password" type="password" class="validate" minlength="8" value="">
					  <label style="width:auto;" for="password">Password</label>
					  <span class="helper-text" data-error="Please enter a new password of at least 8 characters." data-success="Leave blank if you do not wish to change your password.">Leave blank if you do not wish to change your password.</span>
				</div>
			</div>
			<div id="button-wrapper" style="text-align: right;">
				<button class="waves-effect waves-light btn-small" type="submit">Save</button>
			</div>
		</form>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<div id="username_change_modal" class="modal">
	<form name="change-username">
	<div class="modal-content">
		<h3 class="row">Change Username</h3>
		<div class="row" style="margin-bottom: 0;">
			<div class="input-field col s12">
				<input name="old_username" id="old_username" type="text" disabled required class="validate" value="<?php echo get_the_author_meta('user_login',get_current_user_id()); ?>">
				<label for="old_username">Current Username</label>
			</div>
		</div>
		<div class="row" style="margin-bottom: 0;">
			<div class="input-field col s12">
				<input name="old_password" id="old_password" type="password" required class="validate" value="">
				<label for="old_password">Password</label>
				<span class="helper-text">Please enter your password to confirm username change.</span>
			</div>
		</div>
		<div class="row" style="margin-bottom: 0;">
			<div class="input-field col s12">
				<input name="username" id="username" type="text" required class="validate">
				<label for="username">New Username</label>
				<span id="username_help" class="helper-text"></span>
			</div>
		</div>
		<div>You'll have to re-login with your new username after it is changed.</div>
	</div>
	<div class="modal-footer">
		<div id="change-user-button-wrapper" class="text-align:right;"><button class="waves-effect waves-light btn-small" type="submit">Change</button></div>
	</div>
	</form>
</div>
<script>
jQuery('#username').change(function(){
	const username = jQuery('#username').val();
	const username_elem = jQuery('#username')[0];
	if (username == ''){
		jQuery('#username').addClass('invalid');
		jQuery('#username_help').text('Username is required.'); 
	}
	else{
		api('validate_username',{
			data: {
				username
			},
			dataType: 'JSON',
			callback: function(response){
				if (response.code == 1){
					jQuery('#username').removeClass('invalid');
					jQuery('#username').removeClass('valid');
					username_elem.setCustomValidity('Please enter a different username.');
					jQuery('#username_help').text('');
				}
				else if (response.code == 2){
					jQuery('#username').removeClass('invalid');
					jQuery('#username').addClass('valid');
					username_elem.setCustomValidity('');
					jQuery('#username_help').text('');
				}
				else if (response.code == 9){
					jQuery('#username').addClass('invalid');
					jQuery('#username').removeClass('valid');
					username_elem.setCustomValidity('Please enter a different username.');
					jQuery('#username_help').text('Username unavailable.');
				}
			}
		});
	}
});
jQuery('#email').change(function(){
	const email = jQuery('#email').val();
	const email_elem = jQuery("#email")[0];
	const perm_email_help = document.getElementById('perm_email_help').innerHTML;
	if (email == ''){
		jQuery('#email').addClass('invalid');
		jQuery('#email_help').text('Email is required.'); 
	}
	else{
		api('validate_email',{
			data: {
				email
			},
			dataType: 'JSON',
			callback: function(response){
				if (response.code == 1){
					jQuery('#email').removeClass('invalid');
					jQuery('#email').removeClass('valid');
					document.getElementById('email_help').innerHTML = perm_email_help;
					email_elem.setCustomValidity('');
				}
				else if (response.code == 2){
					jQuery('#email').removeClass('invalid');
					jQuery('#email').addClass('valid');
					jQuery('#email_help').text("We'll send you a confirmation email on this address. Your old email will remain active until this one is confirmed.");
					email_elem.setCustomValidity('');
				}
				else if (response.code == 9){
					jQuery('#email').addClass('invalid');
					jQuery('#email').removeClass('valid');
					jQuery('#email_help').text('This Email is unavailable.');
					email_elem.setCustomValidity('Please enter a different Email.');
				}
			}
		});
	}
});
jQuery("form[name='editprofile']").submit(function(event) {
	event.preventDefault();
	spin("#button-wrapper");
	api('update_profile',{
		data: {
			about: jQuery('#about').val(),
			email: jQuery('#email').val(),
			password: jQuery('#password').val()
		},
		dataType: 'JSON',
		callback: function(response){
			document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
			M.Toast.dismissAll();
			jQuery('#password').val('');
			if (response.code === 1){
				M.toast({html: 'Your profile has been updated.'});
				M.toast({html: 'Check your new email for confirmation.'});
			}
			else if (response.code === 2){
				M.toast({html: 'Your profile has been updated.'});
			}
			else if (response.code > 5){
				M.toast({html: 'An error occured.'});
			}
		}
	});
	
});
jQuery("form[name='change-username']").submit(function(event) {
	event.preventDefault();
	document.getElementById("change-user-button-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
	var username = jQuery('#username').val();
	var password = jQuery('#old_password').val();
	api('change_username',{
		data: {new_username:username,password:password},
		callback: function(response){
			document.getElementById("change-user-button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Change</button>';
			M.Toast.dismissAll();
			jQuery('#username').val('');
			jQuery('#old_password').val('');
			if (response == '1'){
				M.toast({html: 'Your username has been succesfully changed.'});
				location.reload();
			}
			else if (response == '5'){
				jQuery('#username_change_modal').modal('close')
			}
			else if (response == '6'){
				M.toast({html: 'The password you entered is incorrect.'});
			}
		}
	});
	
});
</script> 