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
    timeago.cancel();
    timeago.render(document.querySelectorAll('save-time'), 'en_US', { minInterval: 5 });    
}
reRenderSaveTime();
// Send Save Request
let prevWasPerm = false;
let deBounce = null;
window.autosaveDraft = (val,perm) => {
    if (deBounce !== null) {
        clearTimeout(deBounce);
    }
    if (perm) {prevWasPerm = true}
    deBounce = setTimeout(() => {
        const title = window.draftTitle.value === '' ? 'Untitled' : window.draftTitle.value;
        const content = JSON.stringify(val);
        api('save_draft',{
            dataType: 'JSON',
            data: {
                draft_id: document.querySelector('editor').getAttribute('draft_id'),
                title,
                content,
                prevWasPerm
            },
            callback: response => {
                if (response.code > 5) {
                    new toast('An error occured. Your draft may not have been saved.')
                    return;
                }
                // Flags
                window.editedAtAll = false;
                prevWasPerm = false;
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
    set: (val) => {
        window.editedAtAll = true;
        document.querySelector('input[placeholder="Title"]').value = val;
        window.draftTitle.value = val;
        window.autosaveDraft(window.draftContent.value,false);
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
    const originalContent = document.querySelector('load_content').innerText;
    if (originalContent !== '') {
        window.draftContent.set(JSON.parse(originalContent));
    }
    window.draftTitle.set(document.querySelector('load_title').innerText);
    window.editedAtAll = false;
};
loadDraft();
