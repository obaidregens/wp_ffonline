function init_checkbox(){
    const input_checkbox = DOM.qa('checkbox');
    for (let i = 0; i < input_checkbox.length; i++) {
        const elem = input_checkbox[i];
        const input_e = document.createElement('input');
        input_e.setAttribute('type','checkbox');
        const label = document.createElement('label');
        label.classList.add('checkbox');
        const text = document.createElement('text');
        text.innerText = elem.getAttribute('label') || '';
        _.moveAttr(elem,input_e);
    
        label.appendChild(input_e);
        label.appendChild(text);
    
        elem.replaceWith(label);
    }
}
init_checkbox();