DOM.q('input[check-all]').addEventListener('change',event => {
    const cBool = event.target.checked;
    const chks = DOM.qa('label.checkbox > input:not([check-all])');
    for (let i = 0; i < chks.length; i++) {
        chks[i].checked = cBool;
        
    }
});
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
DOM.qa(".disable-update").forEach(el => el.addEventListener('click',async () => {
    if (!await confirmation("Story will no longer be updated automatically.") ) {
        return;
    }
    const response = await api('disable_auto_update',{
        data: {story_id: el.getAttribute('story_id')}
    });
    if (response.code > 5) {
        new toast("An error occured");
        return;
    }
    new toast("Story won't be updated automatically anymore.");
    el.remove();
}));