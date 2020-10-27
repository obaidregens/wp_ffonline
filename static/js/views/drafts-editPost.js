if (DOM.q('button[label="Post"]')) {
    DOM.q('button[label="Post"]').addEventListener('click',({target}) => {
        const dr = DOM.q('editor').getAttribute('draft_id');
        if (dr === 'new') {
            new toast('This draft hasn\'t been saved.');
            return;
        }
        api('post_news',{
            data: {
                draft_id: dr
            }
        })
        .then(response => {
            if (response.code > 5) {
                new toast('An error occured.');
                return;
            }
            new toast('Posted');
            target.setAttribute('disabled','');
        });
    })
}