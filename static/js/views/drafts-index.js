let all_files;
const listing = document.querySelector('folder-listing');
api('get_drafts',{
    callback: response => {
        all_files = response.path;
        loadFolder(current_path);
    }
});
let current_path = '';
const loadFolder = (p) => {
    const folders = Object.keys(all_files);
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
        for (let i = 0; i < (all_files[p] || []).length; i++) {
            const file = all_files[p][i];
            if (typeof OPT_REMOVE_FILES_CLICK === 'undefined') {
                lst = { click: () => window.location.href = '/drafts/' + file.ID + '/edit' }
            };
            listing.appendChild(DOM.create('file',{
                listeners: lst,
                attributes: {
                    draft_id: file.ID
                },
                children: [
                    DOM.create('file-meta',{
                        innerText: file.title
                    })
                ]
            }));
        }
    }
    if ( (all_files[p] || []).length < 1 ) {
        listing.appendChild(DOM.create('span',{
            classes: ['no-drafts'],
            innerText: 'No Drafts yet. '
        }));
        listing.appendChild(DOM.create('a',{
            href: '/drafts/new',
            attributes: typeof OPT_NEW_DRAFT_IN_NEW_TAB === 'undefined' ? {} : {
                target: '_blank'
            },
            innerText: 'Create new'
        }));
    }
    current_path = p;
}