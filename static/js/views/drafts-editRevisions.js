document.querySelector('save-time').addEventListener('click',() => {
    let s = document.querySelector('sidenav[revisions]');
    if (s){
        sidenav.open(s);
    }
    else {
        s = DOM.create('sidenav',{
            attributes: {
                revisions: ''
            }
        });
        sidenav.create(s);
        setTimeout(() => sidenav.open(s));    
    }
    if (window.refreshRevisions || 1===1) {
        api('get_draft_revisions',{
            data: {
                draft_id: document.querySelector('editor').getAttribute('draft_id')
            },
            callback: (response) => {
                if (response.code > 5) {
                    new toast('An error occured');
                    return;
                }
                for (let j = 0; j < response.revisions.length; j++) {
                    const revision = response.revisions[j];
                    s.innerText = '';
                    s.appendChild(DOM.create('li',{
                        
                    }));
                }
                window.refreshRevisions = false;
            }
        });    
    }
});