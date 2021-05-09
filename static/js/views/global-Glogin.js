window.googleSignInRender = () => {
    const signInEl = DOM.create('div',{
        classes: ["google-signin"]
    });
    setTimeout(() => {
        gapi.signin2.render(signInEl, {
            'longtitle': false,
            'onsuccess': async (googleUser) => {
                const token = googleUser.getAuthResponse().id_token;
                const response = await api("google_signin",{
                    data: {
                        token
                    }
                });
                if (response.code > 5) {
                    new toast("An error occured");
                }
                if (response.code === 1) {
					gapi.auth2.getAuthInstance().signOut();
					await idbKeyval.set("ongoingTour","write-intro");
                    window.location.reload();
                    return;
                }
                if (response.code === 2) {
                    prompt_create_username(response);
                }
            },
            'onfailure': () => {}
        });
    });
    return signInEl;
};
function prompt_create_username(existing_data) {
	let code_popup = DOM.q('popup_s[create_username]');
	if (code_popup) {
		popup_s.open(code_popup);
		return;
	}
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
	const submit_button = DOM.create('button',{
		attributes: {
			label: 'Signup'
		},
		listeners: {
			click: function(event) {
				event.preventDefault();
				this.setAttribute('disabled','');
				api('create_username',{
					data: Object.assign(existing_data,{
						username: username_input.querySelector('input').value
					})
				})
				.then(async response => {
					this.removeAttribute('disabled');
					if (response.code === 1){
						gapi.auth2.getAuthInstance().signOut();
						await idbKeyval.set("ongoingTour","write-intro");
						window.location.reload();
					}
					else if (response.code === 7){
						const error_keys = Object.keys(response.errors);
						for (let i = 0; i < error_keys.length; i++) {
							new toast(_.ucfirst(error_keys[i]) + ' - ' + response.errors[error_keys[i]]);
						}
                    }
				});
			}
		}
	});
	code_popup = DOM.create('popup_s',{
		attributes: {
			create_username: ""
		},
		children: [
			DOM.create('form',{
				children: [
					username_input,
					submit_button
				]
			})
		]
	});
	popup_s.create(code_popup);
	popup_s.open(code_popup);
};