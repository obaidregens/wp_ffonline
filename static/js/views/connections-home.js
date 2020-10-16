document.querySelectorAll('connections > a > button').forEach(el => el.addEventListener('click',() => {
    const source = el.parentElement.getAttribute('source');
    document.querySelectorAll(`enter-input`).forEach(ell => ell.classList.remove('show'));
    document.querySelector(`enter-input[source="${source}"]`).classList.add('show');
}));
document.querySelectorAll('enter-input [label="Submit"]').forEach(el => el.addEventListener('click',() => {
    const cont = el.closest('enter-input');
    const source = cont.getAttribute('source');
    api('verify_user',{
        dataType: 'JSON',
        data: {
            source,
            id: cont.querySelector('text-input > input').value
        },
        callback: (response) => {
            if (response.code === 8) {
                new toast("Which user do you want to link?");
                return;
            }
            if (response.code > 5) {
                new toast("An error occured");
                return;
            }
            document.querySelector('completed').innerHTML = `Send this code <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/pm2/post.php?uid=${response.account}&subject=Fanfiction+Online+Verification">here</a>`;
            document.querySelector('verification-code').innerText = response.verification_code;
        }
    });
}));
document.querySelectorAll('verification-code').forEach(cel => cel.addEventListener('click',() => {
    _.copyText(cel.innerText);
    new toast('Copied');
}));
document.querySelectorAll('enter-input .cancel-verification').forEach(el => el.addEventListener('click',() => {
    const cont = el.closest('enter-input');
    const source = cont.getAttribute('source');
    api('cancel_pending_verification',{
        dataType: 'JSON',
        data: {
            source,
        },
        callback: (response) => {
            if (response.code > 5) {
                new toast("An error occured");
                return;
            }
            new toast("Canceled Linking.");
            window.location.reload();
        }
    });
}));