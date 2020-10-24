const loadFolder = rootDraftsIndex('folder-listing');
const drag = new Draggable.Droppable(document.querySelectorAll('folder-listing'), {
    draggable: 'file > file-meta, folder > folder-meta',
    dropzone: 'folder, file:not(.new-draft-file), delete-container',
    distance: 10
});
drag.on('droppable:dropped', event => {
    const is = event.data.dragEvent.data.originalSource.parentElement.tagName.toLowerCase();
    const drop = event.data.dropzone.tagName.toLowerCase();
    const dropZones = (is === "folder" ? ['delete-container'] : ['delete-container','folder']);
    if (! dropZones.includes(drop)) {
        event.cancel();
    }
});
drag.on('droppable:stop', async event => {
    const tgn = event.data.dropzone.tagName.toLowerCase();
    const is = event.data.dragEvent.data.originalSource.parentElement;
    const is_tgn = is.tagName.toLowerCase();
    const draft_id = is.getAttribute('draft_id');
    if (tgn === 'delete-container') {
        const CONFIRM_MESSAGE =
            draft_id ?
            "Are you sure you want to delete this draft?" : 
            "This folder will be deleted. All drafts within will be moved to the current folder.";
        const NOTICE_MESSAGE =
            draft_id ?
            "Your draft was deleted." : 
            "Your folder was deleted";
        const API_CALL = draft_id ? "delete_draft" : "delete_draft_dir";
        const API_DATA = draft_id ? { draft_id } : {path: current_path + "/" + is.querySelector('folder-meta').innerText};
        const res = await confirmation(CONFIRM_MESSAGE);
        if (!res) {
            loadFolder(current_path);
            return;
        }
        await api(API_CALL,{
            data: API_DATA,
        });
        const new_drafts_response = await api('get_drafts');
        const prev_path = current_path;
        all_files = new_drafts_response.path;
        loadFolder(prev_path);
        new toast(NOTICE_MESSAGE);
    }
    else if (tgn === 'folder' && is_tgn === 'file') {
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
                        all_files = response.path;
                        loadFolder(current_path);
                        new toast('Your draft was moved.');
                    }
                });
            }
        });
    }
});
