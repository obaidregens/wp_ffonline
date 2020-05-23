jQuery("form[name='login']").submit(function(event) {
	event.preventDefault();
	var login = jQuery('#login_username').val();
	var password = jQuery('#login_password').val();
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/validate_login.php',
		type: 'post',
		data: {ajax: 1,reCAPTCHA:grecaptcha.getResponse(),login:login,password:password,},
		success: function(response){
			M.Toast.dismissAll();
			grecaptcha.reset();
			if (response == '1'){
				M.toast({html: 'Your username and/or password is incorrect.'});
			}
			else if (response == '8'){
				M.toast({html: 'Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.'});
			}
			else{
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
	if (login.length < 5 || login.length > 20){
		M.toast({html: 'Username has to be between 5-20 characters.'});
		return false;
	}
	if (login.replace(/(\d)|(\.)|(_)|([A-Z])+/gi,'').length > 0){
		M.toast({html: 'Usernames may only contain dots(.), underscores(_), numbers, and letters.'});
		return false;
	}
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
			else if (response == '0'){
				M.toast({html: 'Check your email for confirmation.'});
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