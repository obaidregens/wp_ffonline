if (DOM.q('button')) {
    DOM.q('button').addEventListener('click',function(){
        api('verify_user',{
            data: {
                id: DOM.q('text-input > input').value
            }
        })
        .then(response => {
            if (response.code > 5) {
                return;
            }
            DOM.q('verification-account').innerHTML = `Send this code <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/pm2/post.php?uid=${response.account}">here</a>`;
            DOM.q('verification-code').innerText = response.verification_code;
        });
    });
}
if (DOM.q('verification-code')) { 
    DOM.q('verification-code').addEventListener('click',function(){
        _.copyText(this.innerText);
        new toast('Copied');
    });
}