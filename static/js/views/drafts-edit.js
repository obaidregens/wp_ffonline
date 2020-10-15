window.addEventListener('beforeunload', function (e) {
    if (window.editedAtAll) {
        e.preventDefault();
        e['returnValue'] = '';    
    }
});
const reRenderSaveTime = () =>  {
    const stamp = document.querySelector('save-time').getAttribute('datetime');
    if (parseInt(stamp) === 0) {
        return;
    }
    const nodes = document.querySelectorAll('save-time');
    timeago.cancel(nodes[0]);
    timeago.render(nodes, 'en_US', { minInterval: 5 });    
}
reRenderSaveTime();
// Send Save Request
let deBounce = null;
window.autosaveDraft = (val,perm) => {
    if (deBounce !== null) {
        clearTimeout(deBounce);
    }
    deBounce = setTimeout(() => {
        const title = window.draftTitle.value === '' ? 'Untitled' : window.draftTitle.value;
        const content = val ;
        api('save_draft',{
            dataType: 'JSON',
            data: {
                draft_id: document.querySelector('editor').getAttribute('draft_id'),
                title,
                content,
                perm
            },
            callback: response => {
                if (response.code > 5) {
                    new toast('An error occured while saving your draft. You might need to refresh the page.')
                    return;
                }
                // Flags
                if (response.perm) {
                    window.refreshRevisions = true;
                }
                window.editedAtAll = false;
                // Time
                document.querySelector('save-time').setAttribute('datetime',response.time*1000);
                reRenderSaveTime();
                // Draft ID
                document.querySelector('editor').setAttribute('draft_id',response.draft_id);
                window.history.pushState(
                    "object or string",
                    document.querySelector("title").innerText,
                    '/drafts/' + response.draft_id + '/edit'
                );
            }
        });
    },600);
}
// Draft Content
window.draftTitle = {
    value: '',
    set: (val,initial = false) => {
        window.editedAtAll = true;
        document.querySelector('input[placeholder="Title"]').value = val;
        window.draftTitle.value = val;
        document.title = `${val} - Edit - Drafts - Fanfiction Online`;
        if (!initial) {
            window.autosaveDraft(window.draftContent.value,false);
        }
    }
};
document.querySelector('input[placeholder="Title"]').addEventListener('input',({target}) => window.draftTitle.set(target.value) );
document.querySelector('input[placeholder="Title"]').addEventListener('change',({target}) => {
    if (target.value === '') {
        window.draftTitle.set('Untitled');
    }
});
// Initial Loading
const loadDraft = () => {
    if ( load_content !== null && load_content.length > 0) {
        window.draftContent.set(load_content);
    }
    window.draftTitle.set(load_title,true);
    window.editedAtAll = false;
    window.refreshRevisions = true;
};
loadDraft();