// Folder Listing
let OPT_REMOVE_FILES_CLICK = true;
const folder_listing_pop = DOM.create('popup',{
    attributes: {
        prompt_location_save: ""
    },
    children:[
        DOM.create('folder-listing'),
        DOM.create('button',{
            attributes: {
                label: 'Save'
            }
        })
    ]
});
popup.create(folder_listing_pop);
function newFileSavePopup() {
    return new Promise((resolve, reject) => {
        folder_listing_pop.querySelector('button[label="Save"]').replaceWith(DOM.create('button',{
            attributes: {
                label: 'Save'
            },
            listeners: {
                click: () => {
                    resolve(current_path);
                    popup.close();
                },
                onAfterClose: () => reject(false)
            },
        }));
        popup.open(folder_listing_pop);
    });
}

window.draftTitle = {
    value: '',
    set: (val) => {
        window.editedAtAll = true;
        document.querySelector('input[placeholder="Title"]').value = val;
        window.draftTitle.value = val;
    }
};
document.querySelector('input[placeholder="Title"]').addEventListener('input',({target}) => window.draftTitle.set(target.value) )
const loadDraft = () => {
    window.draftTitle.set(document.querySelector('load_title').innerText);
    const originalContent = document.querySelector('load_content').innerText;
    if (originalContent !== '') {
        window.draftContent.set(JSON.parse(originalContent));
    }
    window.editedAtAll = false;
};
loadDraft();
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
        action: 'delete'
    },
    listeners: {
        click: () => {
            const draft_id = document.querySelector('editor').getAttribute('draft_id');
            if (draft_id === 'new') {
                new toast('This draft hasn\'t been saved.')
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
                    },
                    callback: response => {
                        if (response.code > 5) {
                            new toast('An error occured.');
                            return;
                        }
                        new toast('Draft Deleted');
                        window.location.href = '/drafts';
                    }
                });
            });
        }
    }
}));
document.querySelector('toolbar').appendChild(DOM.create('button',{
    attributes: {
        action: 'share'
    },
    listeners: {
        click: function(event) {
            if (document.querySelector('editor').getAttribute('draft_id') === 'new') {
                new toast("This draft hasn't been saved.");             
                return;
            }
            let _p = document.querySelector('popup[share_draft]');
            if (_p) {
                popup.open(_p);
                return;
            }
            const share_is = document.querySelector('share-is').innerText;
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
                            const _id = document.querySelector('editor').getAttribute('draft_id');
                            if (_id === 'new') {
                                new toast("This draft hasn't been saved.");                               
                                return;
                            }
                            const draftShareRequest = (draft_id) => {
                                const share_el = this.parentElement.querySelector('share-link');
                                const a_el = share_el.querySelector('a.share-link');
                                const share = ! share_el.classList.contains('copy');
                                api('share_draft',{
                                    dataType: 'JSON',
                                    data: {
                                        draft_id,
                                        share
                                    },
                                    callback: (response) => {
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
                                    }
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
                            draftSaveRequest(document.querySelector('editor').getAttribute('draft_id'));
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
function apiSendSaveRequest(draft_id,path = null) {
    return new Promise((resolve, reject) => {
        const title = window.draftTitle.value;
        const content = JSON.stringify(window.draftContent.value);
        api('save_draft',{
            dataType: 'JSON',
            data: {
                title,
                draft_id,
                content,
                path
            },
            callback: response => {
                if (response.code === 8) {
                    new toast('Title?');
                    reject('Title?');
                    return;
                }
                if (response.code === 12) {
                    new toast('Draft with the same title already exists in this folder');
                    reject('Draft with the same title already exists in this folder');
                    return;
                }
                if (response.code === 995) {
                    new toast('Please login to save draft.');
                    reject('Please login to save draft.');
                    return;
                }
                if (response.code > 5) {
                    new toast('An error occured.');
                    reject('An error occured.');
                    return;
                }
                window.editedAtAll = false;
                document.querySelector('autosave-time').innerText = '';
                document.querySelector('editor').setAttribute('draft_id',response.draft_id);
                all_files = response.draft_path;
                loadFolder(current_path);
                if (draft_id === 'new') {
                    new toast('Saved as new draft')
                    window.history.pushState("object or string", document.querySelector("title").innerText,'/drafts/' + response.draft_id + '/edit');
                }
                else {
                    new toast('Saved');
                }
                resolve(response.draft_id);
            },
        });    
    });
}
function draftSaveRequest(draft_id) {
    return new Promise(function(resolve,reject){
        if (draft_id === 'new') {
            newFileSavePopup()
            .then((path) => {
                apiSendSaveRequest(draft_id,path)
                .then(
                    (v) => resolve(v),
                    (v) => reject(v)
                )
            } );
        }
        else {
            apiSendSaveRequest(draft_id)
            .then(
                (v) => resolve(v),
                (v) => reject(v)
            )
        }
    });
}
document.querySelector('button[label="Export"] > dropdown').addEventListener('click', ({target}) => {
    const inner = target.innerText;
    if (! ['FFN','AO3'].includes(inner)){
        return;
    }
    const draft_id = document.querySelector('editor').getAttribute('draft_id');
    if (draft_id === 'new') {
        new toast('Save book first!');
        return;
    }
    window.open('/drafts/' + draft_id + '/export/' + inner.toLowerCase(), '_blank');
});
document.querySelector('button[label="Preview"]').addEventListener('click', ({target}) => {
    const draft_id = document.querySelector('editor').getAttribute('draft_id');
    if (draft_id === 'new') {
        new toast('Save book first!');
        return;
    }
    window.open('/drafts/' + draft_id + '/preview', '_blank');
});
window.addEventListener('keydown',(event) => {
    if (! event.ctrlKey || event.key.toLowerCase() !== 's') {
        return;
    }
    event.preventDefault();
    document.querySelector('toolbar > button[label="Save"] > dropdown > [label="Save"]').dispatchEvent( new Event('click') );
});

// Autosaved
let autosaveData = {
    title: window.draftTitle.value,
    content: window.draftContent.value
};
window.autosaveDraft = val => {
    const title = window.draftTitle.value;
    const content = JSON.stringify(val);
    api('autosave_draft',{
        dataType: 'JSON',
        data: {
            draft_id: document.querySelector('editor').getAttribute('draft_id'),
            title,
            content
        },
        callback: response => {
            if (response.code > 5) {
                return;
            }
            if (!response.time || ! Number.isInteger(response.time)) {
                return;
            }
            const d = new Date(response.time * 1000);
            const display = _t.isToday(d) ? 'Today ' + d.getHours() + ':' + d.getMinutes() + ':' + d.getSeconds() : _t.local(d);
            document.querySelector('autosave-time').innerText = display;
        }
    });
}
const loadAutosave = () => {
    api('load_autosave',{
        dataType: 'JSON',
        data: {
            draft_id: document.querySelector('editor').getAttribute('draft_id')
        },
        callback: ({autosave}) => {
            if (!autosave) {
                return;
            }
            confirmation('An autosave is available. Do you want to use it?')
            .then((res => {
                if (! res ) {
                    return;
                }
                window.draftTitle.set(autosave.title);
                window.draftContent.set(JSON.parse(autosave.content));    
            }));
        }
    });
}
loadAutosave();
window.addEventListener('beforeunload', function (e) {
    if (window.editedAtAll) {
        e.preventDefault();
        e['returnValue'] = '';    
    }
});

if (document.querySelector('button[label="Post"]')) {
    document.querySelector('button[label="Post"]').addEventListener('click',({target}) => {
        const dr = document.querySelector('editor').getAttribute('draft_id');
        if (dr === 'new') {
            new toast('This draft hasn\'t been saved.');
            return;
        }
        api('post_news',{
            dataType: 'JSON',
            data: {
                draft_id: dr
            },
            callback: response => {
                if (response.code > 5) {
                    new toast('An error occured.');
                    return;
                }
                target.setAttribute('disabled','');
            }
        });
    })
}