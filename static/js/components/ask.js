function ask(string,options = null,isRequired = true,prompt = 'Enter',maxlength = null) {
    return new Promise((resolve, reject) => {
        let input_elem ;
        if (options === null) {
            const text_attr = {label: prompt};
            if (maxlength) {
                text_attr.attributes = {
                    maxlength
                };
            }
            input_elem = create_text_input(text_attr);
        }
        else {
            options_el = [];
            for (let i = 0; i < options.length; i++) {
                options_el.push(DOM.create('option',{
                    innerText: options[i].label,
                    attributes: {
                        value: options[i].value
                    }
                }));
            }
            input_elem = document.createElement('select-wrapper');
            input_elem.appendChild(DOM.create('select',{
                children: options_el,
            }));
            input_elem.appendChild(DOM.create('label',{
                attributes: {
                    label: 'Select'
                }
            }))
        }
        const _p = DOM.create('popup_s',{
            attributes: {
                ask: ''
            },
            children: [
                DOM.create('p',{
                    innerText: string
                }),
                input_elem,
                DOM.create('button',{
                    classes: ['popup_close'],
                    attributes: {
                        label: 'OK'
                    },
                    listeners: {
                        click: (event) => {
                            if (input_elem.tagName.toLowerCase() === 'select-wrapper') {
                                resolve(input_elem.querySelector('select').value);
                                return;
                            }
                            const input_v = input_elem.querySelector('input').value;
                            if (input_v === '' && isRequired) {
                                reject(false);
                                return;
                            }
                            resolve(input_v);
                        }
                    }
                }),
            ]
        });
        popup_s.create(_p,{
            onAfterClose: () => {
                reject(false);
                setTimeout(() => _p.remove());
            }
        });
        popup_s.open(_p);
    });
}