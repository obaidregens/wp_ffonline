// Reviews
window.addEventListener('load',() => {
    if (window.is_online === false){
        DOM.q('write-review').style.setProperty('display','none');
    }    
});
DOM.q('reviews-wrapper').addEventListener('click',function(event){
    if (event.target.parentNode.tagName.toLowerCase() !== 'dropdown'){
        return;
    }
    const review = event.target.parentNode.parentNode.parentNode;
    const review_id = review.getAttribute('review_id');
    const label = event.target.getAttribute('label');
    if (label === 'Reply'){
        // Undo quote
        const quote_el = DOM.q('write-review > quote');
        quote_el.innerText = "";

        // Create Clone
        const review_clone = review.cloneNode(true);
        review_clone.querySelector('button').remove();
        review_clone.querySelectorAll('review').forEach(elem => elem.remove());

        // Set Reply
        const reply_elem = DOM.q('write-review > reply-to');
        reply_elem.setAttribute('review_id',review_id);
        reply_elem.removeAttribute('hidden');
        reply_elem.innerHTML = review_clone.innerHTML;

        // Create Cancel
        reply_elem.appendChild(DOM.create('cancel',{
            listeners: {
                click: () => {
                    reply_elem.setAttribute('hidden','');
                    reply_elem.setAttribute('review_id',0);        
                }
            }
        }));
    }
});
const review_submit_btn = DOM.q('write-review > submission > button[label="Submit"]');
if (review_submit_btn){
    review_submit_btn.addEventListener('click',function(){
        const comment_elem = DOM.q('write-review > text-input > textarea');
        this.setAttribute('disabled','');
        const reply_elem = DOM.q('write-review > reply-to');
        const gre = grecaptcha.getResponse();
        api('publish_review',{
            data: {
                chapter_id,
                review_id: parseInt(reply_elem.getAttribute('review_id')),
                content: comment_elem.value,
                quote: DOM.q("write-review > quote").innerText,
            },
            reCAPTCHA: gre
        })
        .then(response => {
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
        });
    });
}
if (DOM.q('write-review')) {
    (() => {
        const submission_el = DOM.q("write-review > submission");
        window.addEventListener('mousedown',event => {
            const currentHeight = parseInt(submission_el.style.height || 0);
            if (! event.target.closest('write-review')) {
                if (currentHeight === 0) {
                    return;
                }
                return submission_el.style.height = 0;
            }
            if (currentHeight !== 0) {
                return;
            }
            _.fitHeight(submission_el);
        });
        DOM.q('write-review > text-input > textarea').addEventListener('input',() => {
            const currentHeight = parseInt(submission_el.style.height || 0);
            if (currentHeight !== 0) {
                return;
            }
            _.fitHeight(submission_el);
        });
    })();
    DOM.q("quote + cancel").addEventListener('click',() => {
        const quote_el = DOM.q('write-review > quote');
        quote_el.innerText = "";
    });
}