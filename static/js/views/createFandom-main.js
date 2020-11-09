const fandoms = JSON.parse(DOM.q('fandoms').innerText);
const inst = new autocomplete(
    DOM.q('text-input > input'),
    fandoms.map(fand => {
        return {name: fand,value: fand};
    } ),{
    select: false,
    none_found_msg: false
});
inst.preChange = () => {
    const btn = DOM.q('button[label="Create"]');
    btn.removeAttribute('disabled');
}
inst.change = ({exactMatch,search}) => {
    if (exactMatch || search === "") {
        const btn = DOM.q('button[label="Create"]');
        btn.setAttribute('disabled',"");
    }
}

DOM.q('button[label="Create"]').addEventListener('click',({target}) => {
    const fandom = DOM.q('text-input > input').value;
    const category = DOM.q('select').value;
    api('create_fandom',{
        data: {
            category,
            fandom
        }
    })
    .then(response => {
        if (response.code === 11) {
            new toast("Fandom with similar name exists.");
            return;
        }
        else if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        target.setAttribute('disabled','');
        new toast('Fandom created.');
    });
});

