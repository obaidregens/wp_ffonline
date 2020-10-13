const reHookOffline = () => {
    document.querySelectorAll('book > options > [label="Offline"]').forEach(btn => {
        const book = btn.closest('book');
        const book_id = book.getAttribute('book_id');
        const total_chapters = parseInt(book.getAttribute('chapters'));
        offlineAPI({
            book_id,
            title: book.querySelector('.title').innerText,
            author: book.querySelector('.author > a').innerText,
            total_chapters,
            elems: [
                btn
            ],
            hooks: {
                started_saving: () => {
                    btn.setAttribute('progress','Saving story');
                },
                save_chapter: (chapter_num,total_chapters) => {
                    btn.setAttribute('progress', `${chapter_num} of ${total_chapters}` );
                },
                finished_saving: () => {
                    btn.removeAttribute('progress');
                }
            }
        });
    });
}
reHookOffline();