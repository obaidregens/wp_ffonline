const reChapterProgress = () => {
    const books_container = DOM.q('books-container');
    if (!books_container){
        return;
    }
    books_container.querySelectorAll(`a.book`).forEach(async el => {
        const sid = el.getAttribute('book_id');
        const item = JSON.parse(await idbKeyval.get('chapter_track-' + sid));
        if (item === null || !item.chapterProgress) {
            return;
        }
        el.querySelector('reading-progress').style.setProperty('--perc',item.chapterProgress + "%");
    });
    // A
    DOM.qa('.story-single').forEach(el => {
        const book = el.querySelector('.book');
        const instSwipe = new Swiper(el);
        el.querySelector('.more').addEventListener('click',E => {
            E.preventDefault();
            E.stopPropagation();
            instSwipe.scroll(instSwipe.index === 1 ? 0 : 1,true);
            return false;
        });
        const maxTrans = Math.min(150,instSwipe.maxTransform);
        instSwipe.maxTransform = maxTrans;
        el.querySelector('div.swiper-slide').style.width = maxTrans+"px";
        instSwipe.on('before-slide',() => {
            if (! book.classList.contains('show')) {
                DOM.qa('books-container .book.show').forEach(ele => ele.classList.remove('show'));
                book.classList.add('show');
            }
        });
    });
}
(() => {
    reChapterProgress();
    const books_container = DOM.q('books-container');
    if (!books_container){
        return;
    }
    books_container.addEventListener('click',({target}) => {
        const options_el = target.closest('options');
        if (!options_el){return;}
        const book = options_el.parentElement.previousElementSibling;
        if (!book){return;}
        const label = target.getAttribute('label');
        if (['Collections','Favorite','Hide'].includes(label) ){
            const book_id = book.getAttribute('book_id');
            collections.open(book_id);
        }
        else if (label === 'Share'){
            const 
                title_elem = book.querySelector('.title'),
                title = title_elem.innerText,
                href = book.querySelector('.title').getAttribute('href'),
                author = book.querySelector('.author a').innerText,
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
// Load Collections
(async () => {
    const res = await api("get_collections",{
        data: {book_ids: [...DOM.qa('books-container a.book')].map(el => el.getAttribute('book_id') )}
    });
    collections.book_collections = res.book_collections || {};
    collections.collections_data = res.collections_data || {};
})();