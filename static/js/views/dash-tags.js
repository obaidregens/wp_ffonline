DOM.q("select").addEventListener('change',({target}) => {
    const url = new URL(window.location.href);
    const newUrl = url.pathname + '?tax=' + target.value.toLowerCase();
    window.location.href = newUrl; 
});
DOM.q('button[label="Create"]').addEventListener('click',() => {
    api('new_tax',{
        data: {
            tax: DOM.q('select').value.toLowerCase(),
            name:DOM.q('text-input > input').value
        }
    })
    .then(response => {
        if (response.code > 5) {
            new toast('An error occured.');
            return;
        }
        window.location.reload();
    });
});