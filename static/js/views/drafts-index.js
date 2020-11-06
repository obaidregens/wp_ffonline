function rootDraftsIndex(root,{OPT_HIDE_DISPLAY_FILES,OPT_REMOVE_FILES_CLICK,OPT_NO_NEW_DRAFT, WITH_WORDS} = {}) {
    const listing = typeof root === 'string' ? DOM.q(root) : root;
    api('get_drafts',{
        data: {
            words: !!WITH_WORDS
        }
    })
    .then(response => {
        window.all_files = response.path;
        loadFolder(window.current_path);
    });
    window.current_path = '';
    const loadFolder = (p) => {
        const folders = Object.keys(window.all_files);
        listing.innerText = '';
        listing.appendChild(DOM.create('delete-container'));
        if (p !== '') {
            listing.appendChild(DOM.create('folder',{
                classes: ['back'],
                listeners: {
                    click: () => {
                        const this_path = p.split('/');
                        this_path.pop();
                        loadFolder(this_path.join('/'));
                    }
                },
                children: [
                    DOM.create('folder-meta')
                ]
            }));
        }
        for (let i = 0; i < folders.length; i++) {
            const path = folders[i];
            let n = path.split('/');
            n.pop();
            n = n.join('/');
            if (n !== p || path === p){continue}
            const portions = path.split('/').filter((v) => v !== '');
            listing.appendChild(DOM.create('folder',{
                listeners: {
                    click: () => loadFolder(path)
                },
                children: [
                    DOM.create('folder-meta',{
                        innerText: portions[portions.length-1]
                    })
                ]
            }));        
        }
        if (typeof OPT_HIDE_DISPLAY_FILES === 'undefined') {
            let lst = {};
            for (let i = 0; i < (window.all_files[p] || []).length; i++) {
                const file = window.all_files[p][i];
                if (typeof OPT_REMOVE_FILES_CLICK === 'undefined') {
                    lst = { click: () => window.location.href = '/drafts/' + file.ID + '/edit' }
                };
                listing.appendChild(DOM.create('file',{
                    listeners: lst,
                    attributes: Object.assign({
                        draft_id: file.ID,
                    },(typeof WITH_WORDS === 'undefined') ? {} : {
                        words: file.words 
                    }),
                    children: [
                        DOM.create('file-meta',{
                            innerText: file.title
                        })
                    ]
                }));
            }
        }
        if (typeof OPT_NO_NEW_DRAFT === "undefined") {
            listing.appendChild(DOM.create('file',{
                classes: ['new-draft-file'],
                listeners: {
                    click: typeof OPT_REMOVE_FILES_CLICK === 'undefined' ? () => {
                        window.location.href = "/drafts/new/edit";
                    } : () => {
                        window.open('/drafts/new/edit/', '_blank');
                    }
                },
                children: [
                    DOM.create('file-meta',{
                        innerText: 'New Draft'
                    })
                ]
            }));    
        }
        window.current_path = p;
    }
    return loadFolder;
}