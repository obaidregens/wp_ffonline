DOM.q('button.new').addEventListener('click',() => popup.open(DOM.q('popup[new-action]')))
DOM.q('[label="Folder"]').addEventListener('click',() => {
    if (current_path.split('/').length >= 3) {
        new toast('Only two subfolders are allowed');
        return;
    }
    ask('New folder',null,true,'Name',40)
    .then(name => {
        api('create_drafts_folder',{
            data: {
                name,
                path: current_path
            }
        })
        .then(response => {
            if ( response.code > 5 ) {
                new toast(response.error_message);
                return;
            }
            new toast('Folder Created');
            all_files = response.drafts;
            loadFolder(current_path);
        });
    });
});