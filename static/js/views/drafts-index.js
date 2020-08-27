let all_files;
// const OPT_REMOVE_FILES_CLICK = true;
const listing = document.querySelector('folder-listing');
const drag = new Draggable.Droppable(document.querySelectorAll('folder-listing'), {
    draggable: 'file > file-meta',
    dropzone: 'folder, file, delete-container',
    distance: 10
});
drag.on('droppable:dropped', event => {
    const tgn = event.data.dropzone.tagName.toLowerCase();
    if (! ['delete-container','folder'].includes(tgn)) {
        event.cancel();
    } 
});
drag.on('droppable:stop', event => {
    const tgn = event.data.dropzone.tagName.toLowerCase();
    const draft_id = event.data.dragEvent.data.originalSource.parentElement.getAttribute('draft_id');
    if (tgn === 'delete-container' ) {
        confirmation('Are you sure you want to delete this draft?')
        .then(
            (res) => {
                if (!res) {
                    loadFolder("");
                    return;
                }
                api('delete_draft',{
                    dataType: 'JSON',
                    data: {
                        draft_id
                    },
                    callback: response => {
                        api('get_drafts',{
                            dataType: 'JSON',
                            callback: response => {
                                all_files = response;
                                loadFolder("");
                                new toast('Your draft was deleted.');
                            }
                        });
                    }
                });
            }
        );
    }
    else if (tgn === 'folder') {
        let path = current_path + '/' + event.data.dropzone.querySelector('folder-meta').innerText;
        if (event.data.dropzone.classList.contains('back')){
            const path_parts = current_path.split('/').filter(v => v !== '');
            path_parts.pop();
            path = path_parts.join('/');
        }
        api('move_draft',{
            dataType: 'JSON',
            data: {
                draft_id,
                path
            },
            callback: response => {
                api('get_drafts',{
                    dataType: 'JSON',
                    callback: response => {
                        all_files = response;
                        loadFolder(current_path);
                        new toast('Your draft was moved.');
                    }
                });
            }
        });
    }
});
api('get_drafts',{
    dataType: 'JSON',
    callback: (response) => {
        all_files = response;
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
    current_path = p;
}