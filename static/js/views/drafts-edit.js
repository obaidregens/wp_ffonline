document.querySelector('input[placeholder="Title"]').addEventListener('input',function(){
    localStorage.setItem('ChapterTitle', this.value);
});
if (document.querySelector('input[placeholder="Title"]').value === '') {
    document.querySelector('input[placeholder="Title"]').value = localStorage.getItem('ChapterTitle') || '';
}
document.querySelector('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'fullscreen'
    },
    listeners: {
        click: function() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }
            document.querySelector('editor').requestFullscreen();
        }
    }
}));
document.querySelector('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'share'
    },
    listeners: {
        click: function(event) {
            let _p = document.querySelector('popup[share_draft]');
            if (_p) {
                popup.open(_p);
                return;
            }
            const children = [
                DOM.create('p',{
                    innerText: 'Let anyone with the link see this draft and propose edits.'
                }),
                DOM.create('share-link',{
                    children: [
                        DOM.create('a',{
                            classes: ['share-link'],
                            attributes: {
                                target: '_blank'
                            }
                        }),
                        DOM.create('button',{
                            listeners: {
                                click: function() {
                                    _.copyText(this.previousElementSibling.href);
                                    new toast('Copied link');
                                    popup.close();
                                }
                            },
                            attributes: {
                                label: 'Copy'
                            }
                        }),        
                    ]
                }),
                DOM.create('button',{
                    attributes: {
                        label: 'Create Link'
                    },
                    listeners: {
                        click: function() {
                            this.setAttribute('disabled','');
                            const draftShareRequest = draft_id => {
                                api('share_draft',{
                                    dataType: 'JSON',
                                    data: {
                                        draft_id
                                    },
                                    callback: (response) => {
                                        this.removeAttribute('disabled');
                                        const share_el = this.parentElement.querySelector('share-link');
                                        share_el.classList.add('copy');
                                        const a_el = share_el.querySelector('a.share-link');
                                        a_el.setAttribute('href',response.link);
                                    }
                                });
                            }
                            const _id = document.querySelector('editor').getAttribute('draft_id');
                            if (_id === 'new') {
                                draftSaveRequest('new')
                                .then(
                                    v => {
                                    draftShareRequest(v);
                                    },
                                    v => popup.close()
                                );
                                return;
                            }
                            draftShareRequest(_id);
                        }
                    },
                    children: [
                        DOM.create('loader',{
                            attributes: {
                                xxs: ''
                            }
                        })
                    ]
                })
            ]
            _p = DOM.create('popup',{
                attributes: {
                    share_draft: ''
                },
                children
            });
            popup.create(_p);
            popup.open(_p);
        }
    }
}));
document.querySelector('toolbar').appendChild(DOM.create('button',{
    attributes: {
        label: 'Save'
    },
    classes: ['dropdown'],
    children: [
        DOM.create('dropdown',{
            classes: ['right'],
            children: [
                DOM.create('li',{
                    attributes: {
                        label: 'Save'
                    },
                    listeners: {
                        click: function() {
                            let _p = document.querySelector('popup[save_confirmation]');
                            if (_p) {
                                popup.open(_p);
                                return;
                            }
                            _p = DOM.create('popup',{
                                attributes: {
                                    save_confirmation: ''
                                },
                                children: [
                                    DOM.create('p',{
                                        innerText: 'Your previous draft will be overwritten. Is that OK?'
                                    }),
                                    DOM.create('button',{
                                        classes: ['popup_close'],
                                        attributes: {
                                            label: 'OK'
                                        },
                                        listeners: {
                                            click: () => {
                                                draftSaveRequest(document.querySelector('editor').getAttribute('draft_id'));
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
                        }
                    }
                }),
                DOM.create('li',{
                    attributes: {
                        label: 'Save New'
                    },
                    listeners: {
                        click: draftSaveRequest.bind(this,'new')
                    }
                })        
            ]
        })
    ]
}));
function draftSaveRequest(draft_id) {
    return new Promise(function(resolve,reject){
        const title = document.querySelector('input[placeholder="Title"]').value;
        api('save_draft',{
            dataType: 'JSON',
            data: {
                title,
                draft_id,
                content: localStorage.getItem('ChapterContent'),
            },
            callback: response => {
                if (response.code === 8) {
                    new toast('Title?');
                    reject('Title?');
                    return;
                }
                if (response.code > 5) {
                    new toast('An error occured.');
                    reject('An error occured.');
                    return;
                }
                document.querySelector('editor').setAttribute('draft_id',response.draft_id);
                if (draft_id === 'new') {
                    window.history.pushState("object or string", document.querySelector("title").innerText,'/drafts/edit/' + response.draft_id);
                }
                new toast('Saved');
                resolve(response.draft_id);
            },
        });
    })
}