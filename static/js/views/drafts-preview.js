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
function set_compare(compare_view) {
    let nt = document.querySelector('next-screen[compare]');
    if (nt) {
        nt.querySelector('compare_text').innerHTML = compare_view;
        next_screen.open(nt);
        return;
    }
    nt = DOM.create('next-screen',{
        attributes: {
            compare: ""
        },
        children: [
            DOM.create('compare_text',{
                innerHTML: compare_view
            })
        ]
    });
    next_screen.create(nt);
    next_screen.open(nt);
}
document.querySelector('popup[select-draft] > drafts').addEventListener('click',({target}) => {
    if (target.parentElement.tagName.toLowerCase() === 'a') {
        target = target.parentElement;
    }
    if (! target.tagName.toLowerCase() === 'a') {
        return;
    }
    const compare = target.getAttribute('draft_id');
    popup.close();
    api('compare_draft',{
        dataType: 'JSON',
        data: {
            draft_id: document.querySelector('button.edit-draft').getAttribute('draft_id'),
            share: document.querySelector('share-link').innerText,
            compare
        },
        callback: response => {
            if (response.code > 5) {
                new toast('An error occured.');
                return;
            }
            set_compare(response.compare_view);
        }
    });
});