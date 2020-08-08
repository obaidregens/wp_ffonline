function confirmation(string) {
    return new Promise((resolve, reject) => {
        _p = DOM.create('popup',{
            attributes: {
                confirmation: ''
            },
            children: [
                DOM.create('p',{
                    innerText: string
                }),
                DOM.create('button',{
                    classes: ['popup_close'],
                    attributes: {
                        label: 'OK'
                    },
                    listeners: {
                        click: () => {
                            resolve(true);
                        }
                    }
                }),
                DOM.create('button',{
                    classes: ['popup_close'],
                    attributes: {
                        label: 'Cancel'
                    }
                })
            ]
        });
        popup.create(_p,{
            onClose: () => {
                resolve(false);
                _p.remove();
            }
        });
        popup.open(_p);
    });
}