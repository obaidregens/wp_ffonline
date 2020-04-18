<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<form name="editprofile" class="col s12 mobile-margin">
			<input type="hidden" id="user_id" name="user_id" value="<?php echo get_current_user_id();?>">
			<input type="hidden" id="username" name="username" value="<?php echo get_the_author_meta('user_login',get_current_user_id()); ?>">
			<div class="row">
				<div class="input-field col s6">
					<input name="first_name" id="first_name" type="text" value="<?php echo get_the_author_meta('user_firstname',get_current_user_id()); ?>">
					<label for="first_name">First Name</label>
				</div>
				<div class="input-field col s6">
					<input name="last_name" id="last_name" type="text" value="<?php echo get_the_author_meta('user_lastname',get_current_user_id()); ?>">
					<label for="last_name">Last Name</label>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<textarea name="about" id="about" class="materialize-textarea"><?php echo get_the_author_meta('user_description',get_current_user_id()); ?></textarea>
					<label for="about">About</label>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<input disabled name="username_d" id="username_d" type="text" value="<?php echo get_the_author_meta('user_login',get_current_user_id()); ?>">
					<label for="username_d">Username</label>
					<span id="username_d_help" class="helper-text">Usernames cannot be changed. Usernames are used for logging in.</span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<input name="pen_name" id="pen_name" type="text" required class="validate" value="<?php echo get_the_author_meta('user_nicename',get_current_user_id()); ?>">
					<label for="pen_name">Pen Name</label>
					<span id="pen_name_help" class="helper-text"></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					  <input name="email" id="email" type="email" class="validate" required value="<?php echo get_the_author_meta('user_email',get_current_user_id()); ?>">
					  <label for="email">Email</label>
					  <span id="email_help" class="helper-text">
						  <?php
							$new_email = get_user_meta( $current_user->ID, '_new_email', true );
							if ( $new_email && $new_email['newemail'] != $current_user->user_email){
								echo 'Please check your inbox at <strong>' . $new_email['newemail'] . '</strong> to confirm your new email.';
							}
							?>
					  </span>
						<span id="perm_email_help" style="display:none">
						  <?php
							$new_email = get_user_meta( $current_user->ID, '_new_email', true );
							if ( $new_email && $new_email['newemail'] != $current_user->user_email){
								echo 'Please check your inbox at <strong>' . $new_email['newemail'] . '</strong> to confirm your new email.';
							}
							?>
						</span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					  <input autocomplete="new-password" name="password" id="password" type="password" class="validate" minlength="8" value="">
					  <label style="width:auto;" for="password">Password</label>
					  <span class="helper-text" data-error="Please enter a new password of at least 8 characters." data-success="Leave blank if you do not wish to change your password.">Leave blank if you do not wish to change your password.</span>
				</div>
			</div>
			<div id="button-wrapper" style="padding-right: 15px;" class="right"><button class="waves-effect waves-light btn-small" type="submit">Save</button></div>
		</form>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<script>
jQuery(document).ready(function(){
    jQuery('#pen_name').keyup(function(){
        var pen_name = jQuery('#pen_name').val();
        if (pen_name == ''){
            jQuery('#pen_name').addClass('invalid');
            jQuery('#pen_name_help').text('Pen Name is required.'); 
        }
        else{
            jQuery.ajax({
                url: '/wp-content/themes/book-writer/php/validate_penname.php',
                type: 'post',
                data: {ajax: 1,pen_name: pen_name},
                success: function(response){
                    if (response == '1'){
                        jQuery('#pen_name').removeClass('invalid');
						jQuery('#pen_name_help').text('');
                        var element = jQuery("#pen_name")[0];
                        element.setCustomValidity('');
                    }
                    else{
                        jQuery('#pen_name').addClass('invalid');
                        jQuery('#pen_name_help').text('This Pen Name is unavailable.');
                        var element = jQuery("#pen_name")[0];
                        element.setCustomValidity('Please enter a different Pen Name.');
                    }
                }
            });
        }
    });
    jQuery('#email').keyup(function(){
        var email = jQuery('#email').val();
		var perm_help = document.getElementById('perm_email_help').innerHTML;
        if (email == ''){
            jQuery('#email').addClass('invalid');
            jQuery('#email_help').text('Email is required.'); 
        }
        else{
            jQuery.ajax({
                url: '/wp-content/themes/book-writer/php/validate_email.php',
                type: 'post',
                data: {ajax: 1,email: email},
                success: function(response){
                    if (response == '1'){
                        jQuery('#email').removeClass('invalid');
						jQuery('#email').removeClass('valid');
						document.getElementById('email_help').innerHTML = perm_help;
                        var element = jQuery("#email")[0];
                        element.setCustomValidity('');
                    }
                    else if (response == '2'){
                        jQuery('#email').removeClass('invalid');
						jQuery('#email_help').text("We'll send you a confirmation email on this address. Your old email will remain active until this one is confirmed.");
                        var element = jQuery("#email")[0];
                        element.setCustomValidity('');
                    }
                    else{
                        jQuery('#email').addClass('invalid');
                        jQuery('#email_help').text('This Email is unavailable.');
                        var element = jQuery("#email")[0];
                        element.setCustomValidity('Please enter a different Email.');
                    }
                }
            });
        }
    });
});
jQuery("form[name='editprofile']").submit(function(event) {
	event.preventDefault();
	document.getElementById("button-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
	var first = jQuery('#first_name').val();
	var last = jQuery('#last_name').val();
	var id = jQuery('#user_id').val();
	var username = jQuery('#username').val();
	var about = jQuery('#about').val();
	var penname = jQuery('#pen_name').val();
	var email = jQuery('#email').val();
	var password = jQuery('#password').val();
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/update_profile.php',
		type: 'post',
		data: {ajax: 1,user_id:id,username:username,pen_name:penname,first_name:first,last_name:last,about:about,email:email,password:password},
		success: function(response){
			document.getElementById("button-wrapper").innerHTML = '<button class="waves-effect waves-light btn-small" type="submit">Save</button>';
			M.Toast.dismissAll();
			M.toast({html: 'Your profile has been updated.'});
			jQuery('#password').val('');
			if (response == '1'){
				M.toast({html: 'Check your new email for confirmation.'});
			}
			else{
				document.getElementById('email_help').innerHTML = '';
			}
		}
	});
	
});
</script>