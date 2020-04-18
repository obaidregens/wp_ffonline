<div id="password-reset-form" >
	<?php if ( $attributes['show_title'] ) : ?>
		<h3><?php _e( 'Pick a New Password', 'personalize-login' ); ?></h3>
	<?php endif; ?>

	<form name="resetpassform" id="resetpassform" action="<?php echo site_url( 'wp-login.php?action=resetpass' ); ?>" method="post" autocomplete="off">
		<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
		<input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />

		<?php if ( count( $attributes['errors'] ) > 0 ) : ?>
			<?php foreach ( $attributes['errors'] as $error ) : ?>
				<p>
					<?php echo $error; ?>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>
		<div class="row">
			<div class="input-field col s12">
				<input autocomplete="new-password" name="pass1" id="pass1" required type="password" class="validate" minlength="8" value="">
				<label for="pass1">Password</label>
				<span class="helper-text" id="pass1_help" data-error="Please enter a new password of at least 8 characters." data-success="">Enter your new password.</span>
			</div>
		</div>
		<div class="row">
			<div class="input-field col s12">
				<input autocomplete="new-password" required name="pass2" id="pass2" type="password" class="validate" value="">
				<label for="pass2">Repeat Password</label>
				<span class="helper-text" id="pass2_help" data-error="Both passwords must be same." data-success="">Enter again to confirm.</span>
			</div>
		</div>
		<button name="submit" id="resetpass-button" class="waves-effect waves-light btn-small right" type="submit">Reset Password</button>
	</form>
</div>
<script>
	jQuery("form[name='resetpassform']").submit(function(event) {
		var pass1 = jQuery('#pass1').val();
		var pass2 = jQuery('#pass2').val();
		if (pass1 != pass2){
			jQuery('#pass2').addClass('invalid');
			var element = jQuery("#pass2")[0];
			return false;
		}
		else{
			return true;
		}
	});
</script>