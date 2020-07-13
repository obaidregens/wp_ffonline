function update_collections(collection_data){
    const _popup = document.createElement('popup');
    _popup.classList.add('collections');
    for (let i = 0; i < collection_data.length; i++) {
        const collection = collection_data[i];
        const _switch = document.createElement('switch');
        _switch.setAttribute('label',collection.title + ' (' +  collection.type + ')');
        _switch.setAttribute('collection_id',collection.ID);
        _popup.appendChild(_switch);
    }
    const new_collection = document.createElement('a');
    new_collection.setAttribute('href','/dashboard/collections');
    new_collection.classList.add('new-collection');
    new_collection.innerText = 'Create Collection';
    _popup.appendChild(new_collection);
    
    _popup.addEventListener('change',function(event){
        if (! event.target.tagName.toLowerCase() === 'input'){
            return;
        }
        const switches = _popup.querySelectorAll('input:checked');
        const collections = [];
        for (let i = 0; i < switches.length; i++) {
            collections.push( switches[i].getAttribute("collection_id") );
        }
        const
            book_id = _popup.getAttribute('book_id'),
            book_collections_elem = document.querySelector('book_collections'),
            _book_collections = JSON.parse(book_collections_elem.innerText);

        _book_collections[book_id] = collections;
        book_collections_elem.innerText = JSON.stringify(_book_collections);

        const data_submit = {
            book_collections: {}
        }
        data_submit.book_collections[book_id] = collections;
        api('add_to_collection',{
            data: {
                data_: JSON.stringify(data_submit)
            },
            callback: function(response) {
            }
        });
    });
    const existing = document.querySelector('popup.collections');
    if (existing){
        existing.replaceWith(_popup);
        return;
    }
    document.documentElement.appendChild(_popup);
    init_switch();
    return _popup;
}
update_collections(JSON.parse(document.querySelector('collections_data').innerText));

function collections_open(book_id) {
    if (! logged_in) {
        prompt_login();
        return;
    }
    const
        collections_popup = document.querySelector('popup.collections'),
        book_collections = JSON.parse(document.querySelector('book_collections').innerText)[book_id];
    collections_popup.setAttribute('book_id',book_id);
    const switches = collections_popup.querySelectorAll('input');
    for (let i = 0; i < switches.length; i++) {
        const switch_elem = switches[i];
        if (book_collections.includes(switch_elem.getAttribute('collection_id'))){
            switch_elem.checked = true;
            continue;
        }
        switch_elem.checked = false;
    }
    popup.open(collections_popup);
}
function share_open({
    title,
    href,
    author,
    desc
}){
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
        const share_popup = document.createElement('popup');
        share_popup.classList.add('share');
        const heading = document.createElement('h1');
        heading.innerText = 'Share';
        share_popup.appendChild(heading);
        const title_elem = document.createElement('book-title');
        title_elem.innerText = title;
        const author_elem = document.createElement('book-author');
        author_elem.innerText = author;
        const desc_elem = document.createElement('book-description');
        desc_elem.innerText = desc;
        share_popup.appendChild(title_elem);
        share_popup.appendChild(author_elem);
        share_popup.appendChild(desc_elem);
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
                    new toast('Copied!');
                });
                continue;
            }
            opt_elem.href = share_opts[opt];
        }
        share_popup.appendChild(share_options_elem);

        popup.create(share_popup,{onClose: function(){
            share_popup.remove()
        }});
        popup.open(share_popup);
    }
}