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
function draftSaveRequest(draft_id) {
    return new Promise(function(resolve,reject){
        const title = window.draftTitle.value;
        const content = JSON.stringify(window.draftContent.value);
        api('save_draft',{
            dataType: 'JSON',
            data: {
                title,
                draft_id,
                content,
            },
            callback: response => {
                if (response.code === 8) {
                    new toast('Title?');
                    reject('Title?');
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

const thesaurus = document.documentElement.appendChild(DOM.create('thesaurus',{
    children: [
        DOM.create('loader',{
            attributes: {
                xxs: ''
            }
        }),
        DOM.create('words')
    ]
}));
document.documentElement.addEventListener('click',({target}) => {
    if ( !thesaurus.contains(target) && thesaurus.classList.contains('open') ) {
        thesaurus.classList.remove('open');
    }
});
function isWordBreak(st) {
    return [' ',',','.',':',';','?','(',')'].includes(st);
}
document.querySelector('editor').addEventListener('keydown',event => {
    if (! event.ctrlKey || event.key !== 'd') {
        return;
    }
    event.preventDefault();
    const sel = window.getSelection();
    const anchor = sel.anchorNode;
    const text = anchor.textContent;
    let b = sel.anchorOffset-1;
    let c = sel.anchorOffset;
    while ( text[b] && ! isWordBreak(text[b]) ) {
        b--;
    }
    while ( text[c] && ! isWordBreak(text[c]) ) {
        c++;
    }
    b++;
    const word = text.slice(b,c);
    const rSel = JSON.parse(JSON.stringify(DraftEditor.selection));

    const cRange = document.createRange();
    cRange.setStart(anchor,b);
    cRange.setEnd(anchor,c);
    rSel.anchor.offset = b;
    rSel.focus.offset = c;

    const co_ordinates = cRange.getBoundingClientRect();

    const left_pos = co_ordinates.left + co_ordinates.width;
    const top_pos = co_ordinates.top + co_ordinates.height;
    thesaurus.classList.remove('empty');
    thesaurus.classList.add('open');
    thesaurus.classList.add('loading');
    thesaurus.style.top = Math.min(top_pos,window.innerHeight-200) + 'px';
    thesaurus.style.left = Math.min(left_pos,window.innerWidth-150) + 'px';
    api('get_synonym',{
        data: {
            word
        },
        dataType: 'JSON',
        callback: response => {
            thesaurus.classList.remove('loading');
            if (response.length === 0) {
                thesaurus.classList.add('empty');
                return;
            }
            const words = thesaurus.querySelector('words');
            words.innerText = '';
            for (let i = 0; i < response.length; i++) {
                words.appendChild(DOM.create('li',{
                    innerText: response[i],
                    attributes: {
                        tabindex: i+1
                    },
                    listeners: {
                        click: ({target}) => {
                            const newWord = target.innerText;
                            const lenDiff = word.length - newWord.length;
                            window.getSelection().collapse(anchor,c+lenDiff);

                            thesaurus.classList.remove('open');
                            DraftEditor.selection = rSel;
                            insertEditorText(newWord);
                        },
                        keydown: (event) => {
                            if (event.key === 'Escape') {
                                event.preventDefault();
                                thesaurus.classList.remove('open');
                                window.getSelection().collapse(anchor,c);
                            }
                            else if (event.key === 'Enter') {
                                event.preventDefault();
                                event.target.dispatchEvent(new Event('click'));
                            }
                            else if (event.key === 'ArrowDown') {
                                event.preventDefault();
                                const currentTabIndex =  parseInt(event.target.getAttribute('tabindex'));
                                const nextTabIndex = currentTabIndex >= response.length ? 1 : currentTabIndex+1;
                                words.querySelector('[tabindex="' + nextTabIndex + '"]').focus();
                            }
                            else if (event.key === 'ArrowUp') {
                                event.preventDefault();
                                const currentTabIndex =  parseInt(event.target.getAttribute('tabindex'));
                                const nextTabIndex = currentTabIndex <= 1 ? response.length : currentTabIndex-1;
                                words.querySelector('[tabindex="' + nextTabIndex + '"]').focus();
                            }
                        }
                    }
                }));
            }
            words.querySelector('[tabindex="1"]').focus();
        }
    });
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
    console.log(window.editedAtAll);
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
                target.setAttribute('disabled');
            }
        });
    })
}