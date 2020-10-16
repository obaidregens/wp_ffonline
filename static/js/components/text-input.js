function init_text_input(){
    const input_fields_text = document.querySelectorAll('text-input');
    for (let i = 0; i < input_fields_text.length; i++) {
        const elem = input_fields_text[i];
        if (elem.children.length !== 0){
            continue;
        }
        const type = elem.getAttribute('type') === 'multi' ? 'textarea' : 'input';
        let input_type = elem.getAttribute('input_type');
        if (! ['email','password','number'].includes(input_type)){
            input_type = 'text';
        }
        const Val = elem.innerText;
        const text_input = create_text_input({
            type,
            label: elem.getAttribute('label'),
            input_type,
            value: Val,
            prefix: elem.getAttribute('prefix')
        });
        let input_e = text_input.querySelector('input');
        if (! input_e) {
            input_e = text_input.querySelector('textarea');
        }

        elem.removeAttribute('label');
        elem.removeAttribute('type');
        elem.removeAttribute('prefix');
    
        _.moveAttr(elem,input_e);
        elem.replaceWith(text_input);
        input_e.dispatchEvent(new Event('change'));
        input_e.dispatchEvent(new Event('input'));
    }
}
init_text_input();
function create_text_input({type = 'input',input_type = 'text',label,value = '',attributes = {},prefix = ""}){
    const listeners = {
        change: function(){
            if (this.value === ''){
                this.classList.remove('filled');
                return;
            }
            this.classList.add('filled');
        }
    };
    if (type === 'textarea'){
        listeners.input = function(){
            if (this.value === ''){
                this.style.height = '43px';
            }
            else {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';    
            }
        }
    }
    const children = [];
    children.push(DOM.create(type,{
        classes: prefix ? ['prefixed'] : [],
        value,
        attributes: Object.assign(attributes,{
            type: input_type,
        }),
        listeners
    }));
    children.push(DOM.create('label',{
        innerText: label
    }));
    if (prefix) {
        children.push(DOM.create('prefix',{
            innerText: prefix
        }));
    }
    return DOM.create('text-input',{
        children
    });
}