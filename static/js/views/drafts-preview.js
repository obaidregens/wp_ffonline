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
            window.location.href = '/drafts/edit/' + response.draft_id;
        }
    });
});