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
        const collection_id = event.target.getAttribute('collection_id');
        const book_id = parseInt(_popup.getAttribute('book_id'));
        const add = event.target.checked;
        api('add_to_collection',{
            data: {
                collection_id,
                book_id,
                add
            },
            callback: response => {
                const el = document.querySelector('book_collections');
                const prev = JSON.parse(el.innerText);
                prev[book_id] = response.book_collections[book_id];
                el.innerText = JSON.stringify(prev);
                collections_open(book_id);
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
        new toast('Login to add story to a collection.')
        return;
    }
    const
        collections_popup = document.querySelector('popup.collections'),
        book_collections = JSON.parse(document.querySelector('book_collections').innerText)[book_id] || [];
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
function share_open(opts){
    opts.callOnCopy = () => new toast('Copied!');
    shareAPI(opts);
}