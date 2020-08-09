document.querySelector('main').addEventListener('change',(event) => {
    const parent = event.target.parentElement;
    if (! ( parent.classList.contains('switch') && parent.tagName.toLowerCase() === 'label') ){
        return;
    }
    const switch_type = event.target.getAttribute('label');
    const book_id = event.target.parentElement.parentElement.getAttribute('book_id');
    if (switch_type === 'Publish') {
        api('book_status',{
            dataType: 'JSON',
            data: {
                book_id,
                status: event.target.checked
            }
        });    
    }
    else if (switch_type === 'Reviews') {
        api('review_status',{
            dataType: 'JSON',
            data: {
                book_id,
                status: event.target.checked
            }
        });    
    }
});