// url
// title
// desc
// author
// callonCopy
const shareAPI = ({
    url,
    title,
    share_title,
    author,
    desc,
    callOnCopy
}) => {
    if (! share_title) {
        share_title = title;
    }
    if (navigator.share) {
        navigator.share({
            title,
            url,
            text: desc
        })
        .then(() => {})
        .catch(console.error);
        return;
    }
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
                setTimeout(() => this.remove(),400);
            }
        }
    });
    const share_options_elem = DOM.create('share-options');
    const share_text = share_title + '%0A%0A' + desc;
    const share_opts = {
        whatsapp: 'https://api.whatsapp.com/send?text=' + share_text + '%0A%0A' + url,
        twitter: 'https://twitter.com/intent/tweet?url=' + url + '&text=' + share_text,
        reddit: 'https://www.reddit.com/submit?url=' + url + '&title=' + title,
        facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + url + '&quote=' + share_text,
        copy: url
    };
    Object.entries(share_opts).forEach(([opt,link]) => {
        const opt_elem = DOM.create('a',{
            classes: [opt],
            attributes: (opt === 'copy') ? {} : {
                href: link,
                target: "_blank"
            },
            listeners: (opt !== 'copy') ? {} : {
                click: () => {
                    _.copyText(link);
                    if (callOnCopy) {
                        callOnCopy();
                    }    
                }
            }
        });
        share_options_elem.appendChild(opt_elem);
    });
    share_popup.appendChild(share_options_elem);
    popup.create(share_popup);
    setTimeout(() => popup.open(share_popup));
}