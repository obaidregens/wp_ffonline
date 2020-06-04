<?php

global $js_bundle;
$js_bundle = global_bundle('reset-password');
$js_bundle->add('reset-password');
$js_bundle->enqueue();


get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
	    <div id="password-reset-form" >
			<h2>Pick a New Password</h2>
			<form name="resetpassform" id="resetpassform" method="post" autocomplete="off">
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
	</main>
</div>
<?php get_footer(); ?>
