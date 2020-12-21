DOM.q('h2[label="Account"] ~ collapsible:nth-of-type(1) > button').addEventListener('click',function(){
    const pass = this.previousElementSibling.previousElementSibling.querySelector('input').value;
    const confirm_pass = this.previousElementSibling.querySelector('input').value;
    api('change_password',{
        data: {
            pass,
            confirm_pass
        }
    })
    .then(response => {
        if (response.code === 1){
            new toast('Password Changed.');
        }
        else if (response.code === 8) {
            new toast('Passwords entered are not the same.');
        }
        else if (response.code === 9) {
            new toast('Password length must be at least 6 and at most, 30 characters.');
        }
        else if (response.code > 5) {
            new toast('An error occured.');
        }
    });
});
DOM.q('h2[label="Account"] ~ collapsible:nth-of-type(2) > button').addEventListener('click',function(){
    const new_username = this.previousElementSibling.previousElementSibling.querySelector('input').value;
    api('change_username',{
        data: {
            new_username
        },
    })
    .then(response => {
        if (response.code === 1){
            new toast('Username Changed.');
            setTimeout( () => window.location.href = "/@" + new_username + "/settings",1000 );
        }
        else if (response.code === 10) {
            Object.entries(response.errors).forEach( ([kk,vv]) => {
                new toast(_.ucfirst(kk) + ' - ' + vv);
            });
        }
        else if (response.code > 5) {
            new toast('An error occured.');
        }
    });
});
DOM.q('h2[label="Account"] ~ collapsible:nth-of-type(2) > text-input > input').addEventListener('input',async ({target}) => {
    const ress = await api('username_check',{data: {
        username: target.value
    }});
    if (ress.code === 1) {
        target.parentElement.removeAttribute('helper');
        target.classList.remove("invalid");
        return;
    }
    target.parentElement.setAttribute('helper',ress.error);
    target.classList.add("invalid");
});
DOM.q('h2[label="Account"] ~ collapsible:nth-of-type(3) > button').addEventListener('click',function(){
    this.setAttribute('disabled','');
    const new_email = this.previousElementSibling.previousElementSibling.querySelector('input').value;
    api('change_email',{
        reCAPTCHA: grecaptcha.getResponse(this.previousElementSibling.getAttribute('widget-id')),
        data: {
            new_email
        },
    })
    .then(response => {
        this.removeAttribute('disabled');
        grecaptcha.reset(this.previousElementSibling.getAttribute('widget-id'));
        if (response.code === 1){
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
            const code_input = DOM.create('code-input',{
                children: code_singles
            });
            const reCAPTCHA_elem = DOM.create('recaptcha');
            const pop = DOM.create('popup',{
                children: [
                    code_input,
                    reCAPTCHA_elem,
                    DOM.create('button',{
                        attributes: {
                            label: "Submit"
                        },
                        listeners: {
                            click: (event) => {
                                event.target.setAttribute('disabled','');
                                api("change_email_confirm",{
                                    reCAPTCHA: grecaptcha.getResponse(reCAPTCHA_elem.getAttribute('widget-id')),
                                    data: {
                                        token: response.token,
                                        code: code_singles.map(elin => elin.value).join(""),
                                        email: new_email
                                    }
                                }).then(response => {
                                    event.target.removeAttribute('disabled');
                                    grecaptcha.reset(reCAPTCHA_elem.getAttribute('widget-id'));
                                    if (response.code === 1) {
                                        new toast("Changed Email");
                                        window.location.reload();
                                    }
                                    if (response.code === 997) {
                                        return new toast("Verify reCAPTCHA.");
                                    }
                                    if (response.code === 9) {
                                        return new toast("The code you entered is invalid.");
                                    }
                                });
                            }
                        }
                    })
                ],
                attributes: {
                    "change_email_confirm": ""
                },
                listeners: {
                    onAfterClose: () => {
                        setTimeout(() => pop.remove(),200);
                    }
                }
            });
            popup.create(pop);
            popup.open(pop);
            init_reCAPTCHA();
        }
        else if (response.code === 10) {
            Object.entries(response.errors).forEach( ([kk,vv]) => {
                new toast(_.ucfirst(kk) + ' - ' + vv);
            });
        }
        else if (response.code === 997) {
            new toast("Verify reCAPTCHA.");
        }
        else if (response.code > 5) {
            new toast('An error occured.');
        }
    });
});
window.addEventListener('load',() => {
    DOM.qa('label.switch > input').forEach(el => el.addEventListener('change',async ({target}) => {
        const response = await api('user_settings',{data: {
            setting: target.getAttribute('setting'),
            set: target.checked
        }});
        if (response.code > 5) {
            new toast("An error occured");
        }
        target.checked = response.set;
    }));
});