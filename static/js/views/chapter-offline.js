offlineAPI({
    book_id,
    title: document.querySelector('book-info > a.title').innerText,
    author: document.querySelector('book-info > author > a').innerText,
    total_chapters: document.querySelectorAll('popup.chapter-index > index > a').length,
    elems: [
        document.querySelector('acs-options > .offline'),
        document.querySelector('chapter > book-options > .book-offline')
    ],
    hooks: {
        started_saving: () => {
            document.querySelector('chapter').appendChild(DOM.create('offline-notice',{
                innerText: "Saving story"
            }));
        },
        save_chapter: (chapter_num,total_chapters) => {
            document.querySelector('chapter > offline-notice').innerText = `Saved ${chapter_num} of ${total_chapters} chapters`;
        },
        finished_saving: () => {
            document.querySelector('chapter > offline-notice').remove();
        }
    }
});