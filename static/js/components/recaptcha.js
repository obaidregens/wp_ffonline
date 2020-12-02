function init_reCAPTCHA(){
    const reCAPTCHA_sitekey = DOM.q('recaptcha-sitekey').innerText;
    const captcha_elems = DOM.qa('reCAPTCHA');
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
window.expose = window.expose || {};
window.expose.init_reCAPTCHA = init_reCAPTCHA;