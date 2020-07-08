jQuery('.scrollspy').scrollSpy();
jQuery('.book-collections').click(function(){
    collections_open(this.getAttribute('book_id'));
});
jQuery('.book-share').click(function(){
    const book_title = document.querySelector('.book-title > a');
    share_open({
        title: book_title.innerText,
        href: book_title.href,
        author: document.querySelector('.book-author > a').innerText,
        desc: document.querySelector('#summary').innerText
    });
});