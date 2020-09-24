document.querySelector('input[check-all]').addEventListener('change',event => {
    const cBool = event.target.checked;
    const chks = document.querySelectorAll('label.checkbox > input:not([check-all])');
    for (let i = 0; i < chks.length; i++) {
        chks[i].checked = cBool;
        
    }
});
document.querySelector('button[label="Import"]').addEventListener('click',event => {
    const inputChecked = document.querySelectorAll('label.checkbox > input:checked:not([check-all])');
    storyIds = [];
    for (let i = 0; i < inputChecked.length; i++) {
        storyIds.push(inputChecked[i].value);
    }
    api('import_stories',{
        data: {
            storyIds
        },
        callback: response => {
            if (response.code > 5) {
                new toast('An error occured.');
                return;
            }
            new toast('Selected stories will be uploaded.');
        }
    });
});