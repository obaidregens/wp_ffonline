const chapter_id = document.querySelector('chapter').getAttribute('chapter_id');
// Reviews
window.addEventListener('load',() => {
    if (typeof grecaptcha === 'undefined'){
        document.querySelector('write-review').style.setProperty('display','none');
    }    
});
document.querySelector('reviews-wrapper').addEventListener('click',function(event){
    if (event.target.parentNode.tagName.toLowerCase() !== 'dropdown'){
        return;
    }
    const review = event.target.parentNode.parentNode.parentNode;
    const review_id = review.getAttribute('review_id');
    const label = event.target.getAttribute('label');
    if (label === 'Reply'){
        const review_clone = review.cloneNode(true);
        review_clone.querySelector('button').remove();
        review_clone.querySelectorAll('review').forEach(elem => {
            elem.remove();
        });;
        const reply_elem = document.querySelector('write-review > reply-to');
        reply_elem.setAttribute('review_id',review_id);
        reply_elem.removeAttribute('hidden');
        reply_elem.innerHTML = review_clone.innerHTML;
        const cancel = document.createElement('cancel');
        cancel.addEventListener('click',function(){
            reply_elem.setAttribute('hidden','');
            reply_elem.setAttribute('review_id',0);
        });
        reply_elem.appendChild(cancel);
    }
});
const review_submit_btn = document.querySelector('write-review > button[label="Submit"]');
if (review_submit_btn){
    review_submit_btn.addEventListener('click',function(){
        const comment_elem = document.querySelector('write-review > text-input > textarea');
        this.setAttribute('disabled','');
        const data = {
            chapter_id,
            content: comment_elem.value,
            action: 'insert'
        };
        const reply_elem = document.querySelector('write-review > reply-to');
        const reply = parseInt(reply_elem.getAttribute('review_id'));
        if (reply !== 0){
            data.review_id = reply;
            data.action = 'reply';
        }
        const gre = grecaptcha.getResponse();
        api('publish_review',{
            data: data,
            dataType: 'JSON',
            reCAPTCHA: gre,
            callback: (response) => {
                this.removeAttribute('disabled');
                if (response.code === 997){
                    new toast('Verify reCAPTCHA.');
                }
                else if (response.code > 5){
                    new toast('An error occured.');
                }
                else if (response.code === 1) {
                    updateReviews();
                }
                comment_elem.value = '';
                comment_elem.dispatchEvent( new Event('input') );
                comment_elem.dispatchEvent( new Event('change') );
                reply_elem.setAttribute('hidden','');
                reply_elem.setAttribute('review_id',0);
                grecaptcha.reset();
            }
        });
    });
}
// Book Options
const book_info = document.querySelector('book-info');
const book_id = book_info.getAttribute('book_id');
document.querySelector('.book-collections').addEventListener('click',function(){
    collections_open(book_id);
});
document.querySelector('.book-share').addEventListener('click',function(){
    const book_title = book_info.querySelector('a.title');
    share_open({
        title: book_title.innerText,
        href: book_title.href,
        author: book_info.querySelector('author > a:first-child').innerText,
        desc: book_info.querySelector('book-description').innerText
    });
});
document.querySelector('.book-vote').addEventListener('click',({target}) => {
    if (! logged_in) {
        new toast('Login to vote for chapter');
        prompt_login();
        return;
    }
    api('vote_chapter',{
        data: {
            chapter_id
        },
        callback: response => {
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
        }
    });
});
