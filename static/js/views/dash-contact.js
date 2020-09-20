document.querySelector('toggle').addEventListener('click',() => {
    document.querySelector('with').classList.toggle('show')
});
const all = JSON.parse(_.htmlspecialchars_decode(document.querySelector('json-data').getAttribute('data')));
const msg_elem = document.querySelector('messages');
document.querySelector('with').addEventListener('click',({target}) => {
    if (target.tagName.toLowerCase() !== "single"){return;}
    const msgs = all[target.innerText];
    msg_elem.parentElement.setAttribute('from',target.innerText);
    msg_elem.innerText = "";
    for (let i = 0; i < msgs.length; i++) {
        const msg = msgs[i];
        msg_elem.appendChild(DOM.create('message',{
            classes: [msg.from === target.innerText ? "other" : "my"],
            innerHTML: msg.message,
            attributes: {
                time: _t.local(new Date(msg.time))
            }
        }));
    }
    document.querySelector('with').classList.remove('show');
});
document.querySelector('button[label="Reply"]').addEventListener('click',() => {
    const to = document.querySelector('message-box').getAttribute('from');
    api('reply_to_contact',{
        data: {
            to,
            message: document.querySelector('message-box > text-input > textarea').value
        },
        callback: response => {
            if (response.code > 5) {
                new toast("An error occured");
                return;
            }
            window.location.reload();
        }
    });
});