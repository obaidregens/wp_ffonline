offlineAPI({
    book_id,
    title: DOM.q('book-info > a.title').innerText,
    author: DOM.q('book-info > author > a').innerText,
    total_chapters: DOM.qa('popup.chapter-index > index > a').length,
    elems: [
        DOM.q('acs-options > .offline'),
        DOM.q('chapter > book-options > .book-offline')
    ],
    hooks: {
        started_saving: () => {
            DOM.q('chapter').appendChild(DOM.create('offline-notice',{
                innerText: "Saving story"
            }));
        },
        save_chapter: (chapter_num,total_chapters) => {
            DOM.q('chapter > offline-notice').innerText = `Saved ${chapter_num} of ${total_chapters} chapters`;
        },
        finished_saving: () => {
            DOM.q('chapter > offline-notice').remove();
        }
    }
});