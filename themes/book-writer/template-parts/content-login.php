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