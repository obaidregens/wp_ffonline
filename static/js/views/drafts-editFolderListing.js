// Folder Listing
let OPT_REMOVE_FILES_CLICK = true;
const folder_listing_pop = DOM.create('popup_s',{
    attributes: {
        prompt_location_save: ""
    },
    children:[
        create_text_input({label: 'Draft name',attributes: {
            maxlength: 80
        }}),
        DOM.create('folder-listing'),
        DOM.create('button',{
            attributes: {
                label: 'Save'
            }
        })
    ]
});
popup_s.create(folder_listing_pop);
function newFileSavePopup() {
    return new Promise((resolve, reject) => {
        folder_listing_pop.querySelector('button[label="Save"]').replaceWith(DOM.create('button',{
            attributes: {
                label: 'Save'
            },
            listeners: {
                click: () => {
                    const draftName = folder_listing_pop.querySelector('text-input > input').value;
                    if (draftName === '') {
                        new toast('Name?');
                        return;
                    }
                    resolve([current_path,draftName]);
                    popup_s.close();
                },
                onAfterClose: () => reject(false)
            },
        }));
        popup_s.open(folder_listing_pop);
    });
}
window.addEventListener('load',event => {
    if (DOM.q('editor').getAttribute('draft_id') === 'new'){
        newFileSavePopup()
        .then(([Path, Name]) => {
            api('create_draft',{
                data: {
                    Path,
                    Name
                }
            })
            .then(response => DOM.q('editor').setAttribute('draft_id',response.draft_id));
        });
    }
});