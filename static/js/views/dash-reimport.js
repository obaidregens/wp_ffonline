DOM.qa('[label="Delete"]').forEach(el => el.addEventListener('click',async () => {
    const user_id = el.parentElement.parentElement.getAttribute('user_id');
    const res = await api("allow_reimport",{data: {
        user_id,
        allow: false
    }});
    if (res.code > 5) {
        new toast("An error occured: " + res.code);
        return;
    }
    window.location.reload();
}));
DOM.qa('[label="Submit"]').forEach(el => el.addEventListener('click',async () => {
    const user_id = DOM.q('text-input > input').value;
    const res = await api("allow_reimport",{data: {
        user_id,
        allow: true
    }});
    if (res.code > 5) {
        new toast("An error occured: " + res.code);
        return;
    }
    window.location.reload();
}));