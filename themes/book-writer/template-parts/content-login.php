<?php

global $user_ID, $user_identity; if (!$user_ID) { ?>
<?php
if (isset($_GET['register'])){
	$register = $_GET['register'];
}
if (isset($_GET['reset'])){
	$reset = $_GET['reset'];
}
?>
<div style="margin-bottom:0;" class="row">
	<div class="col s12">
		<ul class="tabs">
			<li class="tab col s4"><a href="#login_tab">Login</a></li>
			<li class="tab col s4 <?php if ( ! get_option( 'users_can_register' ) ) {echo 'disabled';}?>"><a href="#signup_tab" >Register</a></li>
			<li class="tab col s4"><a href="#forgot_pass_tab">Forgot Password?</a></li>
		</ul>
	</div>
	<div id="login_tab" class="col s12">
		<div class="row"></div>
		<form name="login" class="col s12 mobile-margin" >
			<div class="row">
				<div class="input-field col s12">
					<input name="username" id="login_username" type="text" required class="validate">
					<label for="login_username">Username or Email</label>
					<span id="username_help" class="helper-text" data-error="Please enter your username or email."></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					  <input name="password" id="login_password" type="password" required class="validate">
					  <label for="login_password">Password</label>
					  <span class="helper-text" data-error="Please enter a password."></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<button style="width:100%;" id="login-btn" class="waves-effect waves-light btn-small" type="submit">Login</button>
				</div>
			</div>
		</form>
	</div>
	<?php if ( get_option( 'users_can_register' ) ) {?>
		<div id="signup_tab" class="col s12">
			<div class="row"></div>
			<form name="signup" class="col s12 mobile-margin">
				<div class="row">
					<div class="input-field col s12">
						<input name="username" id="signup_username" type="text" required class="validate">
						<label for="signup_username">Username</label>
						<span id="username_help" class="helper-text" data-error="Please enter a username."></span>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						  <input name="email" id="signup_email" type="email" required class="validate">
						  <label for="signup_email">Email</label>
						  <span class="helper-text" data-error="Please enter an email."></span>
					</div>
				</div>
				<div class="row">
					<div class="input-field col s12">
						<button style="width:100%;" id="signup-btn" class="waves-effect waves-light btn-small" type="submit">Sign up</button>
					</div>
				</div>
			</form>
		</div>
	<?php } ?>
	<div id="forgot_pass_tab" class="col s12">
		<div class="row"></div>
		<form id="lostpasswordform" class="col s12 mobile-margin">
			<div class="row">
				<div class="input-field col s12">
					<input name="user_login" id="user_login" type="text" class="validate">
					<label for="user_login">Username or Email</label>
					<span id="forgot_username_help" class="helper-text"></span>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<button style="width:100%;" id="forgot-pass-btn" class="waves-effect waves-light btn-small" name="submit" type="submit"	>Reset Password</button>
				</div>
			</div>
		</form>
	</div>

	<?php } ?>

</div>
<div id="reCAPTCHA_div" style="margin:0 20px 20px;" class="g-recaptcha" data-sitekey="6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth"></div>
<script type="text/javascript">
	jQuery("form[name='login']").submit(function(event) {
		event.preventDefault();
		var login = jQuery('#login_username').val();
		var password = jQuery('#login_password').val();
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/validate_login.php',
			type: 'post',
			data: {ajax: 1,reCAPTCHA:grecaptcha.getResponse(),login:login,password:password,},
			success: function(response){
				grecaptcha.reset();
				if (response == '1'){
					M.Toast.dismissAll();
					M.toast({html: 'Your username and/or password is incorrect.'});
				}
				else if (response == '8'){
					M.Toast.dismissAll();
					M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
				}
				else{
				    M.Toast.dismissAll();
					jQuery("#login-btn" ).text("Welcome " + response + "!");
					jQuery("#login-btn").addClass("disabled");
					location.reload();
				}
			}
		});
	});
	jQuery("form[name='signup']").submit(function(event) {
		event.preventDefault();
		var login = jQuery('#signup_username').val();
		var email = jQuery('#signup_email').val();
		jQuery.ajax({
			url: '/wp-content/themes/book-writer/php/validate_signup.php',
			type: 'post',
			data: {ajax: 1,reCAPTCHA:grecaptcha.getResponse(),login:login,email:email,},
			success: function(response){
				grecaptcha.reset();
				if (response == '1'){
					M.Toast.dismissAll();
					M.toast({html: 'This username is unavailable'});
				}
				else if(response == '2'){
					M.Toast.dismissAll();
					M.toast({html: 'This email is unavailable'});
				}
				else if(response == '12'){
					M.Toast.dismissAll();
					M.toast({html: 'This username is unavailable'});
					M.toast({html: 'This email is unavailable'});
				}
				else if (response == '8'){
					M.Toast.dismissAll();
					M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
				}
				else{
					jQuery("#signup-btn" ).text("Check your email for confirmation.");
					jQuery("#signup-btn").addClass("disabled");
				}
			}
		});
	});

	jQuery("#lostpasswordform").submit(function(event) {
		event.preventDefault();
		var login = jQuery('#user_login').val();
		if (login == ''){
			jQuery('#user_login').addClass('invalid');
			jQuery('#forgot_username_help').text('Please enter your username or email.');
			return;
		}
		else{
			jQuery.ajax({
				url: '/wp-content/themes/book-writer/php/validate_forgot.php',
				type: 'post',
				data: {ajax: 1,reCAPTCHA:grecaptcha.getResponse(),user_login:login},
				success: function(response){
					grecaptcha.reset();
					if (response == '0'){
						jQuery('#user_login').addClass('invalid');
						jQuery('#forgot_username_help').text("There aren't any authors with this Email or Username.");
					}
					else if (response == '1'){
						jQuery('#forgot-pass-btn').text('Check your email to reset your password');
						jQuery('#forgot_username_help').text('');
						jQuery('#forgot-pass-btn').prop('disabled','true');
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