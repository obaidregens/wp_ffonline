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
