document.querySelector('save-time').addEventListener('click',() => {
    let s = document.querySelector('sidenav[revisions]');
    if (s){
        sidenav.open(s);
    }
    else {
        s = DOM.create('sidenav',{
            attributes: {
                revisions: ''
            }
        });
        sidenav.create(s);
        setTimeout(() => sidenav.open(s));    
    }
    if (window.refreshRevisions) {
        api('get_draft_revisions',{
            data: {
                draft_id: document.querySelector('editor').getAttribute('draft_id')
            },
            callback: (response) => {
                if (response.code > 5) {
                    new toast('An error occured');
                    window.refreshRevisions = true;
                    sidenav.close();
                    return;
                }
                s.innerText = '';
                for (let j = 0; j < response.revisions.length; j++) {
                    const revision = response.revisions[j];
                    const attr = {
                        datetime: parseInt(revision.edited)*1000,
                        revision_id: revision.ID
                    };
                    s.appendChild(DOM.create('li',{
                        listeners: {
                            click: getSingleRevision.bind(null,revision.ID)
                        },
                        attributes: attr
                    }));
                }
                if (response.revisions.length > 0) {
                    timeago.render(s.querySelectorAll('li'), 'en_US', { minInterval: 5 });
                }
                window.refreshRevisions = false;
            }
        });    
    }
});
next_screen.create(DOM.create('next-screen',{
    attributes: {
        "compare-revisions": ""
    },
    listeners: {
        onClose: () => {
            currentCompareRevision = null;
            const li = document.querySelector('sidenav[revisions] > li[selected]');
            if (li) {
                li.removeAttribute('selected');
            }
        }
    },
    children: [
        DOM.create('revision-header',{
            children: [
                DOM.create('button',{
                    classes:  ['close_next-screen'],
                }),
                DOM.create('revision-title'),
                DOM.create('button',{
                    classes: ['select-revision'],
                    listeners: {
                        click: () => document.querySelector('save-time').dispatchEvent(new Event('click'))
                    }
                })
            ]
        }),
        document.createElement('compare'),
        DOM.create('revision-footer',{
            children: [
                DOM.create('button',{
                    attributes: {
                        label: 'Restore'
                    },
                    listeners: {
                        click: () => {
                            if (currentCompareRevision === null) {
                                return;
                            }
                            window.draftContent.set(JSON.parse(currentCompareRevision))
                            next_screen.close();
                        }
                    }
                })
            ]
        })
    ]
}));
let currentCompareRevision = null;
nScreen = document.querySelector('next-screen[compare-revisions] > cross-button').remove();
const getSingleRevision = revision_id => {
    const s = document.querySelector('sidenav[revisions]');
    if (! s) {
        return;
    }
    if (s.querySelector('li[selected]')) {
        s.querySelector('li[selected]').removeAttribute('selected');
    }
    const isLi = s.querySelector('li[revision_id="' + revision_id + '"]');
    isLi.setAttribute('selected','');
    const nScreen = document.querySelector('next-screen[compare-revisions]');
    const titleNode = nScreen.querySelectorAll('revision-title');
    timeago.cancel(titleNode[0]);
    titleNode[0].setAttribute('datetime',isLi.getAttribute('datetime'));
    timeago.render(titleNode, 'en_US', { minInterval: 5 } );

    const n = nScreen.querySelector('compare');
    n.innerText = '';
    n.appendChild(DOM.create('loader',{
        attributes: {
            xl: ""
        }
    }));
    next_screen.open(nScreen);
    api('compare_single_revision',{
        data: {
            draft_id: document.querySelector('editor').getAttribute('draft_id'),
            revision_id
        },
        callback: response => {
            if (response.code > 5) {
                new toast('An error occured');
                return;
            }
            currentCompareRevision = response.revision;
            n.innerHTML = response.compare;
        }
    });    
}