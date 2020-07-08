function init_text_input(){
    const input_fields_text = document.querySelectorAll('text-input');
    for (let i = 0; i < input_fields_text.length; i++) {
        const elem = input_fields_text[i];
        if (elem.children.length !== 0){
            continue;
        }
        const type = elem.getAttribute('type') === 'multi' ? 'textarea' : 'input';
        let input_type = elem.getAttribute('input_type');
        if (! ['email','password'].includes(input_type)){
            input_type = 'text';
        }
        const input_e = document.createElement(type);
        input_e.setAttribute('type',input_type);
        input_e.value = elem.innerText;
        if (type === 'textarea'){
            input_e.addEventListener('input',function(){
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });    
        }
        input_e.addEventListener('change', function (){
            if (this.value === ''){
                this.classList.remove('filled');
                this.style.height = '43px';
                return;
            }
            this.classList.add('filled');
        });
        const label = document.createElement('label');
        label.innerText = elem.getAttribute('label');
        elem.removeAttribute('label');
        elem.removeAttribute('type');
    
        _.moveAttr(elem,input_e);
    
        elem.appendChild(input_e);
        elem.appendChild(label);
    }
}
init_text_input();