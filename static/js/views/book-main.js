const book_id = DOM.q('book').getAttribute('book_id');
DOM.q('.book-collections').addEventListener('click',function(){
    collections_open(book_id);
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