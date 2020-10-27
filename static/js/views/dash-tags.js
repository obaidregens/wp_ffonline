document.querySelector("select").addEventListener('change',({target}) => {
    const url = new URL(window.location.href);
    const newUrl = url.pathname + '?tax=' + target.value.toLowerCase();
    window.location.href = newUrl; 
});
document.querySelector('button[label="Create"]').addEventListener('click',() => {
    api('new_tax',{
        data: {
            tax: document.querySelector('select').value.toLowerCase(),
            name:document.querySelector('text-input > input').value
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