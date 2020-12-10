window.addEventListener('beforeunload', function (e) {
    if (window.editedAtAll) {
        e.preventDefault();
        e['returnValue'] = '';
    }
});
const reRenderSaveTime = (stamp) =>  {
    DOM.q('save-time').setAttribute('datetime',stamp);
    if (parseInt(stamp) === 0) {
        return;
    }
    const nodes = DOM.qa('save-time');
    timeago.cancel(nodes[0]);
    timeago.render(nodes, 'en_US', { minInterval: 5 });    
}
// Send Save Request
let deBounce = null;
window.autosaveDraft = (val,perm) => {
    if (deBounce !== null) {
        clearTimeout(deBounce);
    }
    deBounce = setTimeout(() => {
        const title = window.draftTitle.value === '' ? 'Untitled' : window.draftTitle.value;

        const changes = Object.fromEntries([...new Set(window.editedBlocks)].map(i => [i,val[i]]));
        // console.log([...new Set(window.editedBlocks)],window.mapBlocks.map(({pa,type}) => type + " -> " + pa));
        api('save_draft',{
            data: {
                draft_id: DOM.q('editor').getAttribute('draft_id'),
                title,
                changes,
                maps: window.mapBlocks,
                length: val.length,
                perm
            }
        })
        .then(({code,words,perm,time,draft_id,length}) => {
            if (code > 5) {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }
                new toast('An error occured while saving your draft. You might need to refresh the page.');
                return;
            }
            // Words
            DOM.q('word-count').innerText = words;

            // Flags
            if (perm) {
                window.refreshRevisions = true;
            }
            window.editedAtAll = false;
            window.editedBlocks = [];
            window.mapBlocks = [];
            window.draftLastLength = length;

            // Time
            reRenderSaveTime(time*1000);
            // Draft ID
            DOM.q('editor').setAttribute('draft_id',draft_id);
            window.history.pushState(
                "object or string",
                DOM.q("title").innerText,
                `/drafts/${draft_id}/edit`
            );
        })
        .catch(response => {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            new toast('You\'ve lost connection to the internet, draft not saved.');
        });
    },600);
}
// Title
(() => {
    const title_input = DOM.q('input[placeholder="Title"]');
    window.draftTitle = {
        value: 'Untitled',
        set: (val,initial = false) => {
            window.editedAtAll = true;
            title_input.value = val;
            window.draftTitle.value = val;
            document.title = `${val} - Edit - Drafts - Fanfiction Online`;
            if (!initial) {
                window.autosaveDraft(window.draftContent.value,false);
            }
        }
    };
    title_input.addEventListener('input',
        ({target}) => window.draftTitle.set(target.value)
    );
    title_input.addEventListener('change',({target}) => {
        if (target.value === '') {
            window.draftTitle.set('Untitled');
        }
    });    
})();
// Words
(() => {
    DOM.q('editor').appendChild(
        DOM.create('infobar',{
            children: [
                DOM.create('word-count',{
                    innerText: ""
                })
            ]
        }
    ));    
})();
// Initial Load
(async () => {
    window.editedAtAll = false;
    window.draftLastLength = 0;
    const draft_id = DOM.q('editor').getAttribute('draft_id')
    if (draft_id === "new") {
        return;
    }
    const {
        code,
        title,
        content,
        time,
        words,
        share,
        length
    } = await api("get_draft_data",{data: {
        draft_id
    }});
    if (code > 5) {
        new toast("Your draft couldn't be loaded because of an error.");
        return;
    }
    DOM.q('toolbar [action="share"]').setAttribute('initial-share',share);
    window.draftLastLength = length;
    window.draftContent.set(content);
    window.draftTitle.set(title);
    reRenderSaveTime(time);
    DOM.q('word-count').innerText = words;    
})();