rootDraftsIndex('folder-listing',{OPT_REMOVE_FILES_CLICK: true,OPT_NO_NEW_DRAFT: true});
DOM.q('.edit-draft').addEventListener('click',function(event){
    api('edit_and_save_draft',{
        data: {
            draft_id: this.getAttribute('draft_id'),
            share: DOM.q('share-link').innerText
        }
    })
    .then(response => {
        if (response.code > 5) {
            new toast('An error occured.');
            return;
        }
        window.location.href = '/drafts/' + response.draft_id + '/edit';
    });
});
DOM.q("folder-listing").addEventListener("click",({target}) => {
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
            share: DOM.q("share-link").innerText,
            draft_id: DOM.q('button.edit-draft').getAttribute('draft_id'),
            compare_with: draft_id
        }
    })
    .then(response => {
        let n = DOM.q("next-screen[compare-draft]");
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
    });
    popup_s.close();
});