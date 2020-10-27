if (document.querySelector('author-updates')){
    document.querySelector('author-updates').addEventListener('click',function(event){
        if (event.target.parentElement.tagName.toLowerCase() !== 'dropdown'){
            return;
        }
        const update_id = event.target.parentElement.parentElement.parentElement.getAttribute('update_id');
        const action = event.target.getAttribute('label').toLowerCase();
        api('update_action',{
            data: {
                update_id,
                action
            }
        })
        .then(response => {
            if (response.code > 5){
                new toast('An error occured.');
                return;
            }
            window.location.reload();
        });
    });
}