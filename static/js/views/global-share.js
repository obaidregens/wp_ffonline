const shareAPI = ({
    title,
    href,
    author,
    desc,
    callOnCopy
}) => {
    const share_obj = {
        title: 'I found a great fanfiction by ' + author + ' \'' + title + '\'!',
        url: href,
        text: desc
    };
    if (navigator.share) {
        navigator.share(share_obj).then(() => {
            //
        })
        .catch(console.error);
    }
    else {
        const share_popup = DOM.create('popup',{
            classes: ['share'],
            children: [
                DOM.create('h1',{
                    innerText: 'Share',
                }),
                DOM.create('book-title',{
                    innerText: title
                }),
                DOM.create('book-author',{
                    innerText: author
                }),
                DOM.create('book-description',{
                    innerText: desc
                })
            ],
            listeners: {
                onAfterClose: function() {
                    setTimeout(() => this.remove());
                }
            }
        });
        const share_options_elem = document.createElement('share-options');
        const share_text = share_obj.title + '%0A%0A' + share_obj.text;
        const share_opts = {
            whatsapp: 'https://api.whatsapp.com/send?text=' + share_text + '%0A%0ARead it now: ' + share_obj.url,
            twitter: 'https://twitter.com/intent/tweet?url=' + share_obj.url + '&text=' + share_text,
            reddit: 'https://www.reddit.com/submit?url=' + share_obj.url + '&title=' + share_text,
            facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + share_obj.url + '&quote=' + share_text,
            copy: share_obj.url
        };
        const share_opts_array = Object.keys(share_opts);
        for (let i = 0; i < share_opts_array.length; i++) {
            const opt = share_opts_array[i];
            const opt_elem = document.createElement('a');
            opt_elem.classList.add(opt);
            opt_elem.setAttribute('target','_blank');
            share_options_elem.appendChild(opt_elem);
            if (opt === 'copy'){
                opt_elem.addEventListener('click',function(){
                    _.copyText(share_opts[opt]);
                    if (callOnCopy) {
                        callOnCopy();
                    }
                });
                continue;
            }
            opt_elem.href = share_opts[opt];
        }
        share_popup.appendChild(share_options_elem);

        popup.create(share_popup);
        setTimeout(() => popup.open(share_popup));
    }
}