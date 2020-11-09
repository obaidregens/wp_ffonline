const book_id = DOM.q('book').getAttribute('book_id');
DOM.q('.book-collections').addEventListener('click',function(){
    collections.open(book_id);
});
DOM.q('.book-share').addEventListener('click',function(){
    const book_title = DOM.q('book-title');
    share_open({
        title: book_title.innerText,
        href: window.location.href,
        author: DOM.q('author > a:first-child').innerText,
        desc: DOM.q('book-description').innerText
    });
});
// Load Collections
(async () => {
    const res = await api("get_collections",{
        data: {book_ids: [book_id]}
    });
    collections.book_collections = res.book_collections;
    collections.collections_data = res.collections_data;
})();