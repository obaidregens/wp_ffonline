function confirmation(string) {
    return new Promise((resolve, reject) => {
        _p = DOM.create('popup',{
            attributes: {
                confirmation: ''
            },
            listeners: {
                onAfterClose: () => {
                    resolve(false);
                    setTimeout(() => _p.remove());    
                }
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
        popup.create(_p);
        popup.open(_p);
    });
}