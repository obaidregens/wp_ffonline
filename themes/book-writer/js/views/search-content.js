document.querySelector('books-container').addEventListener('click',function(event){
    // Book Click
    if ( event.target.tagName.toLowerCase() === 'book' ){
        window.location.href = event.target.querySelector('.title').href;
    }

    // Dropdown
    if (event.target.parentElement.tagName.toLowerCase() !== 'dropdown'){
        return;
    }
    const label = event.target.getAttribute('label');
    const book_elem = event.target.parentElement.parentElement.parentElement;
    if (['Collections','Favorite','Hide'].includes(label) ){
        const book_id = book_elem.getAttribute('book_id');
        collections_open(book_id);
    }
    else if (label === 'Share'){
        const 
            title_elem = book_elem.querySelector('a.title'),
            title = title_elem.innerText,
            href = title_elem.href,
            author = book_elem.querySelector('.author > a:first-child').innerText,
            desc = book_elem.querySelector('div.description').innerText;
        share_open({
            title,
            href,
            author,
            desc
        });
    }
});