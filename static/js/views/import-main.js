DOM.q('input[check-all]') && (DOM.q('input[check-all]').addEventListener('change',event => {
    const cBool = event.target.checked;
    const chks = DOM.qa('label.checkbox > input:not([check-all])');
    for (let i = 0; i < chks.length; i++) {
        chks[i].checked = cBool;
        
    }
}));
DOM.q('button[label="Import"]').addEventListener('click',event => {
    const inputChecked = DOM.qa('label.checkbox > input:checked:not([check-all])');
    storyIds = [];
    for (let i = 0; i < inputChecked.length; i++) {
        storyIds.push(inputChecked[i].value);
    }
    api('import_stories',{
        data: {
            storyIds
        }
    })
    .then(response => {
        if (response.code > 5) {
            new toast('An error occured.');
            return;
        }
        new toast('Selected stories will be uploaded.');
    });
});
(() => {
    const confirms = {
        "disable_autoupdate": {
            c: "Story will no longer be updated automatically.",
            t: "Story won't be updated automatically anymore."
        },
        "reimport": {
            c: "Story details and tags edited will be kept but all changes to chapters, including author notes, will be overwrote.",
            t: "Story will be re-imported."
        },
        "cancel_reimport": {
            c: "Story won't be re-imported.",
            t: "Re-imported for story cancelled."
        },
    }
    const status_listener = async ({target}) => {
        const action = target.getAttribute('action');
        if (!confirms[action]) {
            return;
        }
        if (!await confirmation(confirms[action].c) ) {
            return;
        }
        const story_id = target.getAttribute('story_id');
        const response = await api('import_status',{data: {
            action,
            story_id
        }});
        if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        new toast(confirms[action].t);
        if (!response.allow_reimport) {
            target.remove();
            return;
        }
        target.replaceWith(DOM.create("a",{
            attributes: {
                story_id,
                action: (action === "reimport") ? "cancel_reimport" : "reimport"    
            },
            listeners: {
                click: status_listener
            }
        }));
    };
    DOM.qa("[story_id][action]").forEach(el => el.addEventListener('click',status_listener));
})();