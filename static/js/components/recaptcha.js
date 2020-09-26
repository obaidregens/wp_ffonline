function init_reCAPTCHA(){
    window.innerWidth = 512;
    const reCAPTCHA_sitekey = '6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth';
    const captcha_elems = document.querySelectorAll('reCAPTCHA');
    for (let i = 0; i < captcha_elems.length; i++) {
        const wrapper = captcha_elems[i];
        if (wrapper.children.length > 0){
            continue;
        }
        const captcha = document.createElement('div');
        captcha.classList.add('g-recaptcha');
        captcha.setAttribute('data-sitekey',reCAPTCHA_sitekey);
        wrapper.appendChild(captcha);
        if (typeof grecaptcha !== 'undefined'){
            const widgetID = grecaptcha.render(captcha, {
                sitekey: reCAPTCHA_sitekey,
            });
            wrapper.setAttribute('widget-id',widgetID);
        }
    }
}