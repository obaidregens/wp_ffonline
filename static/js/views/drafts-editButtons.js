// Toolbar Buttons
// Fullscreen Button
DOM.q('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'fullscreen'
    },
    listeners: {
        click: function() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }
            DOM.q('editor').requestFullscreen();
        }
    }
}));
// Delete Button
DOM.q('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'delete'
    },
    listeners: {
        click: () => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            const draft_id = DOM.q('editor').getAttribute('draft_id');
            if (draft_id === 'new') {
                new toast('This draft hasn\'t been saved.');
                return;
            }
            confirmation('Are you sure you want to delete this draft?').then((v) => {
                if (!v) {
                    return;
                }
                api('delete_draft',{
                    dataType: 'JSON',
                    data: {
                        draft_id
                    }
                })
                .then(response => {
                    if (response.code > 5) {
                        new toast('An error occured.');
                        return;
                    }
                    new toast('Draft Deleted');
                    window.location.href = '/drafts';
                });
            });
        }
    }
}));
// Preview Button
DOM.q('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'preview'
    },
    listeners: {
        click: () => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            const draft_id = DOM.q('editor').getAttribute('draft_id');
            if (draft_id === 'new') {
                new toast('This draft hasn\'t been saved.');
                return;
            }
            window.open('/drafts/' + draft_id + '/preview', '_blank');        
        }
    }
}));
// Share Button
DOM.q('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'share'
    },
    listeners: {
        click: function(event) {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            if (DOM.q('editor').getAttribute('draft_id') === 'new') {
                new toast("This draft hasn't been saved.");             
                return;
            }
            let _p = DOM.q('popup[share_draft]');
            if (_p) {
                popup.open(_p);
                return;
            }
            const share_is = DOM.q('share-is').innerText;
            const children = [
                DOM.create('p',{
                    innerText: 'Let anyone with the link see this draft and propose edits.'
                }),
                DOM.create('share-link',{
                    classes: share_is === '' ? [] : ['copy'],
                    children: [
                        DOM.create('a',{
                            classes: ['share-link'],
                            attributes: {
                                href: share_is,
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
                        })
                    ]
                }),
                DOM.create('button',{
                    attributes: {
                        label: 'Create Link'
                    },
                    listeners: {
                        click: function() {
                            const _id = DOM.q('editor').getAttribute('draft_id');
                            if (_id === 'new') {
                                new toast("This draft hasn't been saved.");                               
                                return;
                            }
                            const draftShareRequest = (draft_id) => {
                                const share_el = this.parentElement.querySelector('share-link');
                                const a_el = share_el.querySelector('a.share-link');
                                const share = ! share_el.classList.contains('copy');
                                api('share_draft',{
                                    data: {
                                        draft_id,
                                        share
                                    }
                                })
                                .then(response => {
                                    this.removeAttribute('disabled');
                                    if (response.code > 5) {
                                        new toast('An error occured');
                                        return;
                                    }
                                    if (response.code === 2 ) {
                                        share_el.classList.remove('copy');
                                        a_el.setAttribute('href','');
                                        new toast('Disabled Sharing.');
                                        return;
                                    }
                                    share_el.classList.add('copy');
                                    a_el.setAttribute('href',response.link);
                                });
                            }
                            this.setAttribute('disabled','');
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
// Export Button
DOM.q('button[label="Export"] > dropdown').addEventListener('click', ({target}) => {
    const inner = target.innerText;
    if (! ['FFN','AO3'].includes(inner)){
        return;
    }
    const draft_id = DOM.q('editor').getAttribute('draft_id');
    if (draft_id === 'new') {
        new toast('This draft hasn\'t been saved.');
        return;
    }
    window.open('/drafts/' + draft_id + '/export/' + inner.toLowerCase(), '_blank');
});