window.addEventListener('beforeunload', function (e) {
    if (window.editedAtAll) {
        e.preventDefault();
        e['returnValue'] = '';    
    }
});
const reRenderSaveTime = () =>  {
    const stamp = DOM.q('save-time').getAttribute('datetime');
    if (parseInt(stamp) === 0) {
        return;
    }
    const nodes = DOM.qa('save-time');
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
            data: {
                draft_id: DOM.q('editor').getAttribute('draft_id'),
                title,
                content,
                perm
            }
        })
        .then(response => {
            DOM.q('word-count').innerText = response.words;
            if (response.code > 5) {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                new toast('An error occured while saving your draft. You might need to refresh the page.');
                return;
            }
            // Flags
            if (response.perm) {
                window.refreshRevisions = true;
            }
            window.editedAtAll = false;
            // Time
            DOM.q('save-time').setAttribute('datetime',response.time*1000);
            reRenderSaveTime();
            // Draft ID
            DOM.q('editor').setAttribute('draft_id',response.draft_id);
            window.history.pushState(
                "object or string",
                DOM.q("title").innerText,
                '/drafts/' + response.draft_id + '/edit'
            );
        })
        .catch(response => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            new toast('You\'ve lost connection to the internet, draft not saved.');
            return;
        });
    },600);
}
// Draft Content
window.draftTitle = {
    value: '',
    set: (val,initial = false) => {
        window.editedAtAll = true;
        DOM.q('input[placeholder="Title"]').value = val;
        window.draftTitle.value = val;
        document.title = `${val} - Edit - Drafts - Fanfiction Online`;
        if (!initial) {
            window.autosaveDraft(window.draftContent.value,false);
        }
    }
};
DOM.q('input[placeholder="Title"]').addEventListener('input',({target}) => window.draftTitle.set(target.value) );
DOM.q('input[placeholder="Title"]').addEventListener('change',({target}) => {
    if (target.value === '') {
        window.draftTitle.set('Untitled');
    }
});
(() => {
    DOM.q('editor').appendChild(DOM.create('infobar',{
        children: [
            DOM.create('word-count',{
                innerText: DOM.q('words-is').innerText
            })
        ]
    }));
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
})();