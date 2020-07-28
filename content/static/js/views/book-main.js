const book_id = document.querySelector('book').getAttribute('book_id');
document.querySelector('.book-collections').addEventListener('click',function(){
    collections_open(book_id);
});
document.querySelector('.book-share').addEventListener('click',function(){
    const book_title = document.querySelector('book-title');
    share_open({
        title: book_title.innerText,
        href: window.location.href,
        author: document.querySelector('author > a:first-child').innerText,
        desc: document.querySelector('book-description').innerText
    });
});