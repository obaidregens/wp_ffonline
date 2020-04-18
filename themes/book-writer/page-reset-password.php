<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
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
	<div id="reCAPTCHA_div" style="margin:0 20px 20px;" class="g-recaptcha" data-sitekey="6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth"></div>
</div>
<script>
	jQuery("form[name='resetpassform']").submit(function(event) {
	    event.preventDefault();
		var pass1 = jQuery('#pass1').val();
		var pass2 = jQuery('#pass2').val();
		if (pass1 != pass2){
			jQuery('#pass2').addClass('invalid');
			return;
		}
		else{
		    var urlParams = new URLSearchParams(location.search);
		    var key = urlParams.get('key');
		    var login = urlParams.get('login');
			jQuery.ajax({
				url: '/wp-content/themes/book-writer/php/set_password.php',
				type: 'post',
				data: {ajax: 1,key:key,reCAPTCHA:grecaptcha.getResponse(),login:login,password:pass2},
				success: function(response){
				    grecaptcha.reset();
					if (response == '0' || response == '2'){
						M.toast({html: 'An unknown error occured'});
					}
					else if (response == '1'){
					    M.toast({html: 'Your password has been reset.'});
						jQuery('#resetpass-button').prop('disabled','true');
						window.location.href = "https://fanfiction.online/dashboard";
					}
					else if (response == '8'){
						M.Toast.dismissAll();
						M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
					}
				},
			});			
		}
	});
</script>
	</main><!-- .site-main -->


</div><!-- .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
