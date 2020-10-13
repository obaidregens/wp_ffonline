const reChapterProgress = () => {
    const books_container = document.querySelector('books-container');
    books_container.querySelectorAll(`book`).forEach(el => {
        const sid = el.getAttribute('book_id');
        const item = JSON.parse(localStorage.getItem('chapter_track-' + sid));
        if (item === null || !item.chapterProgress) {
            return;
        }
        el.querySelector('reading-progress').style.setProperty('--perc',item.chapterProgress + "%");
    });
}
(() => {
    reChapterProgress();
    const books_container = document.querySelector('books-container');
    if (!books_container){
        return;
    }
    books_container.addEventListener('click',({target}) => {
        const book = target.closest('book');
        if (!book){return;}
        if (! book.classList.contains('show')) {
            books_container.querySelectorAll(`book`).forEach(el => el.classList.remove('show'));
            book.classList.add('show');    
        }
        const label = target.getAttribute('label');
        if (['Read'].includes(label) ){
            const sid = book.getAttribute('book_id');
            const item = JSON.parse(localStorage.getItem('chapter_track-' + sid));
            let appending = "1";
            if (item !== null) {
                appending = item.chapter_num + "#" + item.paragraph;
            }
            localStorage.setItem('chapter_track_follow',JSON.stringify({
                sid,
                timestamp: Date.now()
            }));
            window.location.href = "/story/" + sid + "/" + appending;
        }
        else if (['Collections','Favorite','Hide'].includes(label) ){
            const book_id = book.getAttribute('book_id');
            collections_open(book_id);
        }
        else if (label === 'Share'){
            const 
                title_elem = book.querySelector('a.title'),
                title = title_elem.innerText,
                href = book.querySelector('a.title').getAttribute('href'),
                author = book.querySelector('.author > a').innerText,
                desc = book.querySelector('.description').innerText;
            share_open({
                title,
                href,
                author,
                desc
            });
        }
    });
})();