function update_collections(collection_data){
    const _popup = document.createElement('popup');
    _popup.classList.add('collections');
    for (let i = 0; i < collection_data.length; i++) {
        const collection = collection_data[i];
        const _switch = document.createElement('switch');
        _switch.setAttribute('label',collection.title + ' (' +  collection.type + ')');
        _switch.setAttribute('collection_id',collection.ID);
        const edit_attrs = {
            "tooltip-top": 'Edit'
        };
        if (['Favorites','Hidden'].includes(collection.title)){
            edit_attrs.disabled = "";
        }
        _popup.appendChild(_switch);
        const collection_options =  DOM.append(DOM.create('collection-options'),[
            DOM.create('a',{
                href: collection.link || '/collections/' + collection.ID,
                attributes: {
                    "tooltip-top": "View",
                    "target": '_blank'
                }
            }),
            DOM.create('a',{
                listeners: {
                    click: function(event){
                        create_collection_open(collection.ID);
                    }
                },
                attributes: edit_attrs
            })
        ]);
        _popup.appendChild(DOM.append(DOM.create('collection'),[
            _switch,
            collection_options
        ]));
    }
    const new_collection = document.createElement('a');
    new_collection.addEventListener('click',function(){
        create_collection_open();
    })
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
        init_switch();
        return;
    }
    document.documentElement.appendChild(_popup);
    init_switch();
    return _popup;
}
if (document.querySelector('collections_data')){
    update_collections(JSON.parse(document.querySelector('collections_data').innerText));
}

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
function create_collection_open(collection_id = 'new'){
    let cc_popup = document.querySelector('popup[update_collection]');
    const open_collection_popup = document.querySelector('popup.show.collections[book_id]');
    const prev_book_id = open_collection_popup ? open_collection_popup.getAttribute('book_id') : null;
    function open_cmd_only(){
        cc_popup.setAttribute('collection_id',collection_id);
        const prefill = {
            title: '',
            privacy: 'Public'
        };
        if (collection_id !== 'new'){
            const collections_o = JSON.parse(document.querySelector('collections_data').innerText);
            for (let i = 0; i < collections_o.length; i++) {
                const collection_obj = collections_o[i];
                if (collection_obj.ID === collection_id){
                    prefill.title = collection_obj.title;
                    prefill.privacy = collection_obj.type;
                    break;
                }
            }
        }
        cc_popup.querySelector('text-input > input').value = prefill.title;
        cc_popup.querySelector('text-input > input').dispatchEvent(new Event('change'));
        cc_popup.querySelector('select').value = prefill.privacy;
        if (prev_book_id){
            cc_popup.setAttribute('prev_book_id',prev_book_id);
        }
		popup.open(cc_popup);
    }
	if (cc_popup) {
        open_cmd_only();
		return;
    }
    cc_popup = DOM.create('popup',{
        attributes: {
            update_collection: '',
            collection_id
        }
    });
    if (prev_book_id){
        cc_popup.setAttribute('prev_book_id',prev_book_id);
    }
    const privacySelect = document.createElement('select');
    const privacyOptionsLabel = ['Public','Unlisted','Private'];
    for (let i = 0; i < privacyOptionsLabel.length; i++) {
        privacySelect.appendChild(DOM.create('option',{
            innerText: privacyOptionsLabel[i],
            attributes: {
                value: privacyOptionsLabel[i]
            }
        }));
    }
    function on_collection_submit(event){
        const to_delete = event.target.getAttribute('label') === 'Delete';
        const c_id = cc_popup.getAttribute('collection_id') || 'new';
        const book_collections_elem = document.querySelector('book_collections');
        api('update_collection',{
            data: {
                delete: to_delete,
                collection_id: cc_popup.getAttribute('collection_id') || 'new',
                book_ids: Object.keys(JSON.parse(book_collections_elem.innerText)),
                title: cc_popup.querySelector('text-input > input').value,
                privacy: privacySelect.value
            },
            dataType: 'JSON',
            callback: function(response) {
                if (response.code === 1 || response.code === 2){
                    if (response.code === 2){
                        new toast('Collection Deleted');
                    }
                    else if (c_id === 'new'){
                        new toast('Collection Created');
                    }
                    else {
                        new toast('Collection Updated');
                    }
                    update_collections(response.collections_data);
                    book_collections_elem.innerText = JSON.stringify(response.book_collections);
                    document.querySelector('collections_data').innerText = JSON.stringify(response.collections_data);    
                }
                else {
                    new toast('An error occured');
                }
                collections_open(cc_popup.getAttribute('prev_book_id'));
            }
        });
    }
    cc_popup = DOM.append(cc_popup,[
        DOM.create('text-input',{
            attributes: {
                label: 'Collection Name',
            }
        }),
        privacySelect,
        DOM.create('label',{
            attributes: {
                label: 'Privacy'
            }
        }),
        DOM.create('button',{
            attributes: {
                label: 'Save',
                theme: ''
            },
            listeners: {
                click: on_collection_submit
            }
        }),
        DOM.create('button',{
            attributes: {
                label: 'Delete',
                theme: ''
            },
            listeners: {
                click: on_collection_submit
            }
        }),
        DOM.create('a',{
            classes: ['back'],
            listeners: {
                click: function(){
                    collections_open(cc_popup.getAttribute('prev_book_id'));
                }
            }
        })
    ]);
    popup.create(cc_popup);
	init_text_input();
	open_cmd_only();
}