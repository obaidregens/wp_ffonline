// function existing_chapters_open() {
//     let p = document.querySelector('popup[existing_chapters]');
//     popup.open(p);
//     return;
// }
// document.querySelector('submit > [label="Chapters"]').addEventListener('click',() => {
//     if (! selected.book_id || selected.book_id === 'new') {
//         new toast('Save book first!');
//         return;
//     }
// });
document.querySelector('submit > [label="Save"]').addEventListener('click',({target}) => {
    target.setAttribute('disabled','');
    api('edit_book',{
        dataType: 'JSON',
        data: selected,
        callback: response => {
            target.removeAttribute('disabled');
            if (response.code > 5) {
                new toast('An error occured.');
                return;
            }
            new toast('Book Updated');
        }
    })
});