offlineAPI({
    book_id,
    title: DOM.q('book-header > book-title').innerText,
    author: DOM.q('book-header > author > a').innerText,
    total_chapters: DOM.qa('chapter-index > a').length,
    elems: [
        DOM.q('book-options > .book-offline'),
    ],
    hooks: {
        started_saving: () => {
            DOM.q('book-more').appendChild(DOM.create('offline-notice',{
                innerText: "Saving story"
            }));
        },
        save_chapter: (chapter_num,total_chapters) => {
            DOM.q('book-more > offline-notice').innerText = `Saved ${chapter_num} of ${total_chapters} chapters`;
        },
        finished_saving: () => {
            DOM.q('book-more > offline-notice').remove();
        }
    }
});