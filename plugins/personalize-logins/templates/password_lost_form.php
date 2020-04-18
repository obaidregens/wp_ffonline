<div id="password-lost-form">
	<?php if ( $attributes['show_title'] ) : ?>
		<h3><?php _e( 'Forgot Your Password?', 'personalize-login' ); ?></h3>
	<?php endif; ?>

	<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
		<?php foreach ( $attributes['errors'] as $error ) : ?>
			<p>
				<?php echo $error; ?>
			</p>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="row"></div>
	<form id="lostpasswordform" class="col s12 mobile-margin" action="<?php echo wp_lostpassword_url(); ?>" method="post">
		<div class="row">
			<div class="input-field col s12">
				<input name="user_login" id="user_login" type="text" class="validate">
				<label for="user_login">Username or Email</label>
				<span id="forgot_username_help" class="helper-text"></span>
			</div>
		</div>
		<div class="row">
			<div class="input-field col s12">
				<button style="width:100%;" id="forgot-pass-btn" class="waves-effect waves-light btn-small" name="submit" type="submit"	<?php if($_GET['checkemail'] == 'confirm'){echo ' disabled>Check your email to reset your password';} else{echo '>Reset Password';}?></button>
			</div>
		</div>
	</form>
</div>
<script>
	var returner;
	jQuery("#lostpasswordform").submit(function(event) {
		var login = jQuery('#user_login').val();
		if (login == ''){
			jQuery('#user_login').addClass('invalid');
			jQuery('#forgot_username_help').text('Please enter your username or email.');
			return false;
		}
		else{
			jQuery.ajax({
				url: '/wp-content/themes/book-writer/php/validate_forgot.php',
				type: 'post',
				data: {ajax: 1,login:login},
				success: function(response){
					if (response == '0'){
						jQuery('#user_login').addClass('invalid');
						jQuery('#forgot_username_help').text("There aren't any authors with this Email or Username.");
						returner = false;
					}
					else if (response == '1'){
						returner = true;
					}
				},
				async: false
			});
			return returner;
		}
	});
</script>