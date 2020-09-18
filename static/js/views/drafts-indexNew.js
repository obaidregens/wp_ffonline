document.querySelector('button.new').addEventListener('click',() => popup.open(document.querySelector('popup[new-action]')))
document.querySelector('[label="Folder"]').addEventListener('click',() => {
    if (current_path.split('/').length >= 3) {
        new toast('Only two subfolders are allowed');
        return;
    }
    ask('New folder',null,true,'Name',40)
    .then(name => {
        api('create_drafts_folder',{
            dataType: 'JSON',
            data: {
                name,
                path: current_path
            },
            callback: response => {
                if ( response.code > 5 ) {
                    new toast(response.error_message);
                    return;
                }
                new toast('Folder Created');
                all_files = response.drafts;
                loadFolder(current_path);
            }
        })
    });
});