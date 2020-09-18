rootDraftsIndex('folder-listing',{OPT_REMOVE_FILES_CLICK: true,OPT_NO_NEW_DRAFT: true});
document.querySelector('.edit-draft').addEventListener('click',function(event){
    api('edit_and_save_draft',{
        dataType: 'JSON',
        data: {
            draft_id: this.getAttribute('draft_id'),
            share: document.querySelector('share-link').innerText
        },
        callback: response => {
            if (response.code > 5) {
                new toast('An error occured.');
                return;
            }
            window.location.href = '/drafts/' + response.draft_id + '/edit';
        }
    });
});
document.querySelector("folder-listing").addEventListener("click",({target}) => {
    let file = null;
    if (target.tagName.toLowerCase() === 'file') {
        file = target;
    }
    if (target.parentElement.tagName.toLowerCase() === 'file') {
        file = target.parentElement;
    }
    if (file === null || file.classList.contains('new-draft-file') ) {
        return;
    }
    const draft_id = file.getAttribute('draft_id');
    api('compare_draft',{
        data: {
            share: document.querySelector("share-link").innerText,
            draft_id: document.querySelector('button.edit-draft').getAttribute('draft_id'),
            compare_with: draft_id
        },
        callback: response => {
            let n = document.querySelector("next-screen[compare-draft]");
            if (n){
                n.querySelector('compare').innerHTML = response.compare;
                next_screen.open(n);
                return;
            }
            n = DOM.create("next-screen",{
                attributes: {
                    "compare-draft": ""
                },
                children: [
                    DOM.create("compare",{
                        innerHTML: response.compare
                    })
                ]
            })
            next_screen.create(n);
            next_screen.open(n);
        }
    });
    popup.close();
});