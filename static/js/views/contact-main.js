document.querySelector('button').addEventListener('click',function(){
    const message = document.querySelector('text-input > textarea').value;
    if (message === ''){
        new toast("What's your message?")
        return;
    }
    this.setAttribute('disabled','');
    api('contact',{
        dataType: 'JSON',
        reCAPTCHA: grecaptcha.getResponse(document.querySelector('recaptcha').getAttribute('widget-id')),
        data: {
            email: document.querySelector('text-input > input').value,
            message
        },
        callback: (response) => {
            this.removeAttribute('disabled');
            if (response.code === 997){
                new toast ('Please verify yourself by clicking on the \"I\'m not a robot\" checkbox.');
                return;
            }
            new toast('Message Sent.');
        }
    });
});