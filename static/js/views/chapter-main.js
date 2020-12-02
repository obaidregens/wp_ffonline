const chapter_id = DOM.q('chapter').getAttribute('chapter_id');
// Book Options
const book_info = DOM.q('book-info');
const book_id = book_info.getAttribute('book_id');
DOM.q('.book-collections').addEventListener('click',function(){
    collections.open(book_id);
});
DOM.q('.book-share').addEventListener('click',function(){
    const book_title = book_info.querySelector('a.title');
    share_open({
        title: book_title.innerText,
        href: book_title.href,
        author: book_info.querySelector('author > a:first-child').innerText,
        desc: book_info.querySelector('book-description').innerText
    });
});
DOM.q('.book-vote').addEventListener('click',({target}) => {
    if (! logged_in) {
        new toast('Login to vote for chapter');
        prompt_login();
        return;
    }
    api('vote_chapter',{
        data: {
            chapter_id
        }
    })
    .then(response => {
        if (response.code === 11) {
            new toast("You can't vote on your story");
            return;
        }
        if (response.code > 5) {
            new toast("An error occured");
            return;
        }
        if (response.code === 2) {
            target.classList.remove('active');
        }
        else if (response.code === 1){
            target.classList.add('active');
        }
    });
});
// NoSleep
(() => {
    const noSleep = new NoSleep();
    window.addEventListener('click', function enableNoSleep() {
        window.removeEventListener('click', enableNoSleep, false);
        noSleep.enable();
    }, false);
})();
// Load Collections
(async () => {
    const res = await api("get_collections",{
        data: {book_ids: [book_id]}
    });
    collections.book_collections = res.book_collections;
    collections.collections_data = res.collections_data;
})();