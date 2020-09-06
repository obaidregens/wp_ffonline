const fandoms = JSON.parse(document.querySelector('fandoms').innerText);
const onEnterInput = ({target}) => {
    const btn = document.querySelector('button[label="Create"]');
    btn.removeAttribute('disabled');
    removeFandomOptions();
    const s = target.value.toLowerCase().trim();
    const list = [];
    let l = true;
    for (let i = 0; i < fandoms.length; i++) {
        const t = fandoms[i].toLowerCase().trim();
        if (t.search(s) !== -1) {
            list.push(fandoms[i]);
        }
        if (t === s) {l = false;}
    }
    if (list.length === 0) {
        return;
    }
    if (l === false || s === '') {
        btn.setAttribute('disabled','');
    }
    const el = DOM.create('ul',{
        classes: ['fandom-options'],
        children: list.map(v => {
            return DOM.create('li',{
                innerText: v
            });
        })
    });
    document.documentElement.appendChild(el);
    const rect = target.getBoundingClientRect();
    el.style.top = rect.bottom + "px";
    el.style.width = rect.width + "px";
    el.style.left = rect.left + "px";
};
const removeFandomOptions = () => {
    if (document.querySelector('ul.fandom-options')) {
        document.querySelector('ul.fandom-options').remove();
    }
}
document.querySelector('text-input > input').addEventListener('focus',onEnterInput);
document.querySelector('text-input > input').addEventListener('input',onEnterInput);
document.querySelector('text-input > input').addEventListener('blur',removeFandomOptions);
window.addEventListener('resize',removeFandomOptions);

document.querySelector('button[label="Create"]').addEventListener('click',({target}) => {
    const fandom = document.querySelector('text-input > input').value;
    const category = document.querySelector('select').value;
    api('create_fandom',{
        data: {
            category,
            fandom
        },
        callback: response => {
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
        }
    });
});