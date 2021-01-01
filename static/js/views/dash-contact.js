DOM.q('toggle').addEventListener('click',() => {
    DOM.q('with').classList.toggle('show')
});
const msg_elem = DOM.q('messages');
DOM.q('with').addEventListener('click',({target}) => {
    if (target.tagName.toLowerCase() !== "single-from"){return;}
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
    DOM.q('with').classList.remove('show');
    _scroll.documentTop = _scroll.documentHeight;
});
DOM.q('button[label="Reply"]').addEventListener('click',() => {
    const to = DOM.q('message-box').getAttribute('from');
    api('reply_to_contact',{
        data: {
            to,
            message: DOM.q('message-box > text-input > textarea').value
        }
    })
    .then(response => {
        if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        window.location.reload();
    });
});
DOM.qa("single > button").forEach(el => el.addEventListener('click',async ({target}) => {
    const from = target.previousElementSibling.innerText;
    await api("archive_from_contact",{data: {
        from
    }});
    window.location.reload();
}));