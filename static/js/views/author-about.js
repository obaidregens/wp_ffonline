if (document.querySelector('.edit-about')) {
    document.querySelector('.edit-about').addEventListener('click',function(){
        const bio = document.querySelector('author-bio > content');
        const bio_inner = bio ? bio.innerText : '';
        const textarea = document.querySelector('.edit-about ~ text-input > textarea');
        textarea.value = bio_inner;
        textarea.dispatchEvent(new Event('change'));
        textarea.dispatchEvent(new Event('input'));
        this.classList.add('edit');
    });
    document.querySelector('.edit-about ~ button[label="Cancel"]').addEventListener('click',function(){
        document.querySelector('.edit-about').classList.remove('edit');
    });
    document.querySelector('.edit-about ~ button[label="Save"]').addEventListener('click',function(){
        api('update_bio',{
            dataType: 'JSON',
            data: {
                user: document.querySelector('author-main').getAttribute('user_id'),
                bio: document.querySelector('.edit-about ~ text-input > textarea').value
            },
            callback: function(response){
                if (response.code > 5){
                    new toast('An error occured.');
                    return;
                }
                window.location.reload();
            }
        });
    });
}