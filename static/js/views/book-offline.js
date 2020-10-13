offlineAPI({
    book_id,
    title: document.querySelector('book-header > book-title').innerText,
    author: document.querySelector('book-header > author > a').innerText,
    total_chapters: document.querySelectorAll('chapter-index > collapsible > index > a').length,
    elems: [
        document.querySelector('book-options > .book-offline'),
    ],
    hooks: {
        started_saving: () => {
            document.querySelector('book-more').appendChild(DOM.create('offline-notice',{
                innerText: "Saving story"
            }));
        },
        save_chapter: (chapter_num,total_chapters) => {
            document.querySelector('book-more > offline-notice').innerText = `Saved ${chapter_num} of ${total_chapters} chapters`;
        },
        finished_saving: () => {
            document.querySelector('book-more > offline-notice').remove();
        }
    }
});