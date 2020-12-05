function prompt_login() {
	if (window.is_online === false) {
		new toast("You're offline.");
		return;
	}
	let login_popup = DOM.q('popup[login]');
	if (login_popup) {
		popup.open(login_popup);
		return;
	}
	login_popup = DOM.create('popup',{
		attributes: {
			login: ""
		}
	});
	const form = DOM.create('form');
	const login_input = create_text_input({
		label: 'Username or Email',
		attributes: {
			name: "username"
		}
	});
	const password_input = create_text_input({
		label: 'Password',
		input_type: 'password',
		attributes: {
			name: 'password'
		}
	});
	const reCAPTCHA_elem = DOM.create('recaptcha');
	const submit_btn = DOM.create('button',{
		attributes: {
			label: "Login"
		}
	});
	const signup_btn = DOM.create('a',{
		attributes: {
			label: "Signup",
			onclick: "window.expose.prompt_signup()"
		},
	});
	const forgot_btn = DOM.create('a',{
		attributes: {
			"label": "Send OTP",
			"onclick": "window.expose.prompt_forgot()"
		}
	});

	submit_btn.addEventListener('click',function(event){
		event.preventDefault();
		const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
		const data_submit = {
			username: login_input.querySelector('input').value,
			password: password_input.querySelector('input').value
		};
		submit_btn.setAttribute('disabled','');
		api('login',{
			reCAPTCHA: grecaptcha.getResponse(widgetID),
			data: data_submit
		})
		.then(response => {
			submit_btn.removeAttribute('disabled');
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
				window.location.reload();
			}
		});
	});

	form.appendChild(login_input);
	form.appendChild(password_input);
	form.appendChild(reCAPTCHA_elem);
	form.appendChild(submit_btn);
	login_popup.appendChild(form);
	login_popup.appendChild(window.googleSignInRender());
	login_popup.appendChild(forgot_btn);
	login_popup.appendChild(signup_btn);

	popup.create(login_popup);
	init_reCAPTCHA();
	popup.close();
	popup.open(login_popup);
}
function prompt_signup() {
	let signup_popup = DOM.q('popup[signup]');
	if (signup_popup) {
		popup.close();
		popup.open(signup_popup);
		return;
	}
	signup_popup =  DOM.create('popup');
	signup_popup.setAttribute('signup','');
	const form = DOM.create('form');

	const username_input = DOM.update(create_text_input({
		prefix: "@",
		label: 'Username',
	}),{
		attributes: {
			name: 'username'
		}
	});
	username_input.querySelector('input').addEventListener('input',async ({target}) => {
		const ress = await api('username_check',{data: {
			username: target.value
		}});
		if (ress.code === 1) {
			username_input.removeAttribute('helper');
			target.classList.remove("invalid");
			return;
		}
		username_input.setAttribute('helper',ress.error);
		target.classList.add("invalid");
	});
	const email_input = DOM.update(create_text_input({
		label: 'Email',
		input_type: 'email'
	}),{
		attributes: {
			name: 'email'
		}
	});
	const reCAPTCHA_elem = DOM.create('recaptcha');
	const submit_btn = DOM.create('button');
	submit_btn.setAttribute('label','Signup');
	const login_btn = DOM.create('a');
	login_btn.setAttribute('label','Back to Login.');
	login_btn.setAttribute('onclick','window.expose.prompt_login()');

	submit_btn.addEventListener('click',function(event){
		event.preventDefault();
		const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
		const data_submit = {
			username: username_input.querySelector('input').value,
			email: email_input.querySelector('input').value
		};
		submit_btn.setAttribute('disabled','');
		api('signup',{
			reCAPTCHA: grecaptcha.getResponse(widgetID),
			data: data_submit
		})
		.then(response => {
			submit_btn.removeAttribute('disabled');
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
				new toast('The verification code has been sent to your email.');
				data_submit.token = response.token;
				data_submit.action = 'signup';
				prompt_email_code(data_submit);
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
	let forgot_popup = DOM.q('popup[forgot]');
	if (forgot_popup) {
		popup.close();
		popup.open(forgot_popup);
		return;
	}
	forgot_popup = DOM.create('popup');
	forgot_popup.setAttribute('forgot','');
	const form = DOM.create('form');
	const login_input = DOM.update(create_text_input({
		label: 'Username or Email',
	}),{
		attributes: {
			name: 'username'
		}
	});
	const reCAPTCHA_elem = DOM.create('recaptcha');
	const submit_btn = DOM.create('button');
	submit_btn.setAttribute('label','Send OTP');
	const login_btn = DOM.create('a');
	login_btn.setAttribute('label','Back to Login');
	login_btn.setAttribute('onclick','window.expose.prompt_login()');

	submit_btn.addEventListener('click',function(event){
		event.preventDefault();
		const data_submit = {
			username: login_input.querySelector('input').value,
		};	
		const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
		submit_btn.setAttribute('disabled','');
		api('login_with_code',{
			reCAPTCHA: grecaptcha.getResponse(widgetID),
			data: data_submit
		})
		.then(response => {
			submit_btn.removeAttribute('disabled');
			grecaptcha.reset(widgetID);
			if (response.code === 997){
				new toast('Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.');
			}
			else if (response.code === 1){
				new toast('If email/username exists, you\'ll be sent a code.');
				data_submit.token = response.token;
				data_submit.action = 'login_with_code';
				prompt_email_code(data_submit);
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
function prompt_email_code(existing_data) {
	let code_popup = DOM.q('popup[email_code]');
	if (code_popup) {
		popup.open(code_popup);
		return;
	}
	const code_singles = [];
	for (let i = 0; i < 8; i++) {
		code_singles.push(DOM.create('input',{
			attributes: {
				maxlength: 1
			},
			listeners: {
				keydown: (event) => {
					const inp = event.target;
					if ( ["ArrowRight"].includes(event.key) ) {
						if (inp.nextElementSibling) {
							inp.nextElementSibling.focus();
						}	
					}
					else if ( ["ArrowLeft"].includes(event.key) ) {
						if (inp.previousElementSibling) {
							inp.previousElementSibling.focus();
						}
					}
					else if ( ["Backspace"].includes(event.key) ) {
						if (event.target.value !== "") {
							event.target.value = "";
							event.preventDefault();
							return;
						}
						if (inp.previousElementSibling) {
							inp.previousElementSibling.focus();
						}
					}
				},
				paste: event => {
					const txt = event.clipboardData.getData('Text').substr(0,8);
					for (let i = 0; i < txt.length; i++) {
						code_singles[i].value = txt[i];
					}
				},
				input: event => {
					if (event.inputType === "insertText" && event.target.nextElementSibling) {
						event.target.nextElementSibling.focus();
					}
				}
			}
		}));
	}
	window.cc = code_singles;
	const code_input = DOM.create('code-input',{
		children: code_singles
	});
	const reCAPTCHA_elem = DOM.create('recaptcha');
	const submit_button = DOM.create('button',{
		attributes: {
			label: 'Verify'
		},
		listeners: {
			click: function(event) {
				event.preventDefault();
				existing_data.code = code_singles.map(elin => elin.value).join("");
				const widgetID = reCAPTCHA_elem.getAttribute('widget-id');
				this.setAttribute('disabled','');
				api('verify_code',{
					reCAPTCHA: grecaptcha.getResponse(widgetID),
					data: existing_data
				})
				.then(response => {
					this.removeAttribute('disabled');
					grecaptcha.reset(widgetID);
					if (response.code === 1){
						window.location.reload();
					}
					else if (response.code === 7){
						new toast('Invalid Code');
					}
					else if (response.code > 5){
						new toast('An error occured.');
					}
				});
			}
		}
	});
	code_popup = DOM.create('popup',{
		attributes: {
			email_code: ""
		},
		children: [
			DOM.create('form',{
				children: [
					code_input,
					reCAPTCHA_elem,
					submit_button
				]
			})
		]
	});
	popup.create(code_popup);
	init_reCAPTCHA();
	popup.open(code_popup);
}
window.expose = window.expose || {};
window.expose.prompt_login = prompt_login;
window.expose.prompt_forgot = prompt_forgot;
window.expose.prompt_signup = prompt_signup;