document.querySelector('button').addEventListener('click',function(){
    const rcp = document.querySelector('main > recaptcha');
    const message = document.querySelector('text-input > textarea').value;
    if (message === ''){
        new toast("What's your message?")
        return;
    }
    this.setAttribute('disabled','');
    api('contact',{
        reCAPTCHA: grecaptcha.getResponse(rcp.getAttribute('widget-id')),
        data: {
            email: document.querySelector('text-input > input').value,
            message
        }
    })
    .then(response => {
        grecaptcha.reset(rcp.getAttribute('widget-id'));
        this.removeAttribute('disabled');
        if (response.code === 8) {
            new toast("What's your message?");
            return;
        }
        if (response.code === 9) {
            new toast("Enter a valid email.");
            return;
        }
        if (response.code === 997){
            new toast ('Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.');
            return;
        }
        if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        new toast('Message Sent');
    });
});