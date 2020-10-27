DOM.qa('connections > a > button').forEach(el => el.addEventListener('click',() => {
    const source = el.parentElement.getAttribute('source');
    DOM.qa(`enter-input`).forEach(ell => ell.classList.remove('show'));
    DOM.q(`enter-input[source="${source}"]`).classList.add('show');
}));
DOM.qa('enter-input [label="Submit"]').forEach(el => el.addEventListener('click',() => {
    const cont = el.closest('enter-input');
    const source = cont.getAttribute('source');
    api('verify_user',{
        data: {
            source,
            id: cont.querySelector('text-input > input').value
        }
    })
    .then(response => {
        if (response.code === 8) {
            new toast("Which user do you want to link?");
            return;
        }
        if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        DOM.q('completed').innerHTML = `Send this code <a rel="nofollow" target="_blank" href="https://www.fanfiction.net/pm2/post.php?uid=${response.account}&subject=Fanfiction+Online+Verification">here</a>`;
        DOM.q('verification-code').innerText = response.verification_code;
    });
}));
DOM.qa('verification-code').forEach(cel => cel.addEventListener('click',() => {
    _.copyText(cel.innerText);
    new toast('Copied');
}));
DOM.qa('enter-input .cancel-verification').forEach(el => el.addEventListener('click',() => {
    const cont = el.closest('enter-input');
    const source = cont.getAttribute('source');
    api('cancel_pending_verification',{
        data: {
            source,
        }
    })
    .then(response => {
        if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        new toast("Canceled Linking.");
        window.location.reload();
    });
}));