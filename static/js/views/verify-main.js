document.querySelector('button').addEventListener('click',function(){
    api('verify_user',{
        dataType: 'JSON',
        data: {
            id: document.querySelector('text-input > input').value
        },
        callback: (response) => {
            if (response.code > 5) {
                return;
            }
            document.querySelector('verification-account').innerHTML = `Send this code <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/pm2/post.php?uid=${response.account}">here</a>`;
            document.querySelector('verification-code').innerText = response.verification_code;
        }
    });
})
document.querySelector('verification-code').addEventListener('click',function(){
    _.copyText(this.innerText);
    new toast('Copied');
});