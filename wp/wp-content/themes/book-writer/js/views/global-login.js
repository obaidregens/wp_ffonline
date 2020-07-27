function prompt_login() {
	let login_popup = document.querySelector('popup[login]');
	if (login_popup) {
		popup.close();
		popup.open(login_popup);
		return;
	}
	login_popup =  document.createElement('popup');
	login_popup.setAttribute('login','');
	const form = document.createElement('form');
	const login_input = DOM.update(create_text_input({
		label: 'Username or Email',
	}),{
		attributes: {
			name: 'username'
		}
	});
	const password_input = DOM.update(create_text_input({
		label: 'Password',
		input_type: 'password'
	}),{
		attributes: {
			name: 'password'
		}
	});
	const reCAPTCHA_elem = document.createElement('recaptcha');
	const submit_btn = document.createElement('button');
	submit_btn.setAttribute('label','Login');
	const signup_btn = document.createElement('a');
	signup_btn.setAttribute('label','Signup');
	signup_btn.setAttribute('onclick','prompt_signup()');
	const forgot_btn = document.createElement('a');
	forgot_btn.setAttribute('label','Forgot Password');
	forgot_btn.setAttribute('onclick','prompt_forgot()');

	submit_btn.addEventListener('click',function(event){
		event.preventDefault();
		const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
		api('validate_login',{
			reCAPTCHA: grecaptcha.getResponse(widgetID),
			dataType: 'JSON',
			data: {
				username: login_input.querySelector('input').value,
				password: password_input.querySelector('input').value
			},
			callback: function(response){
				grecaptcha.reset(widgetID);
				if (response.code === 7){
					new toast('Your username and/or password is incorrect.');
				}
				else if (response.code === 997){
					new toast('Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.');
				}
				else if (response.code > 5) {
					new toast('An error occured.');
				}
				else if (response.code <= 5){
					location.reload();
				}
			}
		});
	});

	form.appendChild(login_input);
	form.appendChild(password_input);
	form.appendChild(reCAPTCHA_elem);
	form.appendChild(submit_btn);
	login_popup.appendChild(form);
	login_popup.appendChild(forgot_btn);
	login_popup.appendChild(signup_btn);

	popup.create(login_popup);
	init_reCAPTCHA();
	popup.close();
	popup.open(login_popup);
}
function prompt_signup() {
	let signup_popup = document.querySelector('popup[signup]');
	if (signup_popup) {
		popup.close();
		popup.open(signup_popup);
		return;
	}
	signup_popup =  document.createElement('popup');
	signup_popup.setAttribute('signup','');
	const form = document.createElement('form');

	const username_input = DOM.update(create_text_input({
		label: 'Username',
	}),{
		attributes: {
			name: 'username'
		}
	});
	const email_input = DOM.update(create_text_input({
		label: 'Email',
		input_type: 'email'
	}),{
		attributes: {
			name: 'email'
		}
	});
	const reCAPTCHA_elem = document.createElement('recaptcha');
	const submit_btn = document.createElement('button');
	submit_btn.setAttribute('label','Signup');
	const login_btn = document.createElement('a');
	login_btn.setAttribute('label','Back to Login.');
	login_btn.setAttribute('onclick','prompt_login()');

	submit_btn.addEventListener('click',function(event){
		event.preventDefault();
		const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
		api('validate_signup',{
			reCAPTCHA: grecaptcha.getResponse(widgetID),
			dataType: 'JSON',
			data: {
				username: username_input.querySelector('input').value,
				email: email_input.querySelector('input').value
			},
			callback: function(response){
				grecaptcha.reset(widgetID);
				if (response.code === 997){
					new toast('Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.');
				}
				else if (response.code === 7){
					const error_keys = Object.keys(response.errors);
					for (let i = 0; i < error_keys.length; i++) {
						new toast(_.ucfirst(error_keys[i]) + ' - ' + response.errors[error_keys[i]]);
					}
				}
				else if (response.code === 1){
					new toast('Check your email for confirmation.');
				}
			}
		});
	});

	form.appendChild(username_input);
	form.appendChild(email_input);
	form.appendChild(reCAPTCHA_elem);
	form.appendChild(submit_btn);
	signup_popup.appendChild(form);
	signup_popup.appendChild(login_btn);

	popup.create(signup_popup);
	init_text_input();
	init_reCAPTCHA();
	popup.close();
	popup.open(signup_popup);
}
function prompt_forgot() {
	let forgot_popup = document.querySelector('popup[forgot]');
	if (forgot_popup) {
		popup.close();
		popup.open(forgot_popup);
		return;
	}
	forgot_popup = document.createElement('popup');
	forgot_popup.setAttribute('forgot','');
	const form = document.createElement('form');
	const login_input = DOM.update(create_text_input({
		label: 'Username or Email',
	}),{
		attributes: {
			name: 'username'
		}
	});
	const reCAPTCHA_elem = document.createElement('recaptcha');
	const submit_btn = document.createElement('button');
	submit_btn.setAttribute('label','Recover Password');
	const login_btn = document.createElement('a');
	login_btn.setAttribute('label','Back to Login');
	login_btn.setAttribute('onclick','prompt_login()');

	submit_btn.addEventListener('click',function(event){
		event.preventDefault();
		const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
		api('validate_forgot',{
			reCAPTCHA: grecaptcha.getResponse(widgetID),
			dataType: 'JSON',
			data: {
				username: login_input.querySelector('input').value,
			},
			callback: function(response){
				grecaptcha.reset(widgetID);
				if (response.code === 997){
					new toast('Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.');
				}
				else if (response.code === 1){
					new toast('If your email or username matches an account, we\'ll send you a recovery email.');
				}
			}
		});
	});

	form.appendChild(login_input);
	form.appendChild(reCAPTCHA_elem);
	form.appendChild(submit_btn);
	forgot_popup.appendChild(form);
	forgot_popup.appendChild(login_btn);

	popup.create(forgot_popup);
	init_text_input();
	init_reCAPTCHA();
	popup.close();
	popup.open(forgot_popup);
}