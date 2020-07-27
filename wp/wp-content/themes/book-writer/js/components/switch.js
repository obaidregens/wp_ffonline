function init_switch(){
    const input_switch = document.querySelectorAll('switch');
    for (let i = 0; i < input_switch.length; i++) {
        const elem = input_switch[i];
        const input_e = document.createElement('input');
        input_e.setAttribute('type','checkbox');
        const label = document.createElement('label');
        label.classList.add('switch');
        const text = document.createElement('text');
        text.innerText = elem.getAttribute('label') || '';
        _.moveAttr(elem,input_e);
        label.appendChild(input_e);
        label.appendChild(text);
    
        elem.replaceWith(label);
    }
}
init_switch();