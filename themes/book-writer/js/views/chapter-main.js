const chapter_id = document.querySelector('chapter').getAttribute('chapter_id');
// Reviews
document.querySelector('reviews').addEventListener('click',function(){
    if (event.target.parentNode.tagName.toLowerCase() !== 'dropdown'){
        return;
    }
    const review = event.target.parentNode.parentNode.parentNode;
    const review_id = review.getAttribute('review_id');
    const label = event.target.getAttribute('label');
    if (label === 'Delete'){
        api('post_comment',{
            data: {
                chapter_id,
                action: 'delete',
                id: review_id
            },
            dataType: 'JSON',
            callback: (response) => {
                if (response.code === 6){
                    // Login
                }
                else if (response.code > 5){
                    // An error occured
                }
                else if (response.code === 1){
                    document.querySelector('reviews').innerHTML = response.comments;
                }
            }
        });
    }
    else if (label === 'Reply'){
        const review_clone = review.cloneNode(true);
        review_clone.querySelector('button').remove();
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
            comment: comment_elem.value,
            action: 'insert'
        };
        const reply_elem = document.querySelector('write-review > reply-to');
        const reply = parseInt(reply_elem.getAttribute('review_id'));
        if (reply !== 0){
            data.id = reply;
            data.action = 'reply';
        }
        api('post_comment',{
            data: data,
            dataType: 'JSON',
            reCAPTCHA: grecaptcha.getResponse(),
            callback: (response) => {
                if (response.code === 6){
                    prompt_login();
                }
                else if (response.code === 997){
                    new toast('Verify reCAPTCHA.');
                }
                else if (response.code > 5){
                    new toast('An error occured.');
                }
                else if (response.code === 1){
                    document.querySelector('reviews').innerHTML = response.comments;
                }
                this.removeAttribute('disabled');
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