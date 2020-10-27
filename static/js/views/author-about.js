if (DOM.q('.edit-about')) {
    DOM.q('.edit-about').addEventListener('click',function(){
        const bio = DOM.q('author-bio > content');
        const bio_inner = bio ? bio.innerText : '';
        const textarea = DOM.q('.edit-about ~ text-input > textarea');
        textarea.value = bio_inner;
        textarea.dispatchEvent(new Event('change'));
        textarea.dispatchEvent(new Event('input'));
        this.classList.add('edit');
    });
    DOM.q('.edit-about ~ button[label="Cancel"]').addEventListener('click',function(){
        DOM.q('.edit-about').classList.remove('edit');
    });
    DOM.q('.edit-about ~ button[label="Save"]').addEventListener('click',async function(){
        const response = await api('update_bio',{
            data: {
                user: DOM.q('author-main').getAttribute('user_id'),
                bio: DOM.q('.edit-about ~ text-input > textarea').value
            }
        });
        if (response.code > 5){
            new toast('An error occured.');
            return;
        }
        window.location.reload();
    });
}