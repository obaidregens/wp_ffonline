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
        const collection_name = event.target.getAttribute('label');
        const book_id = parseInt(_popup.getAttribute('book_id'));
        const add = event.target.checked;
        api('add_to_collection',{
            data: {
                collection_id,
                book_id,
                add
            },
            callback: response => {
                if (collection_name === "Hidden (Private)" && add) {
                    new toast("Story will be hidden in your next search.");
                }
                const prev = _.clone(window.book_collections);
                prev[book_id] = response.book_collections[book_id];
                window.book_collections = prev;
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
if (_.clone(window.collections_data)){
    update_collections(_.clone(window.collections_data));
}

function collections_open(book_id) {
    if (! logged_in) {
        prompt_login();
        new toast('Login to add story to a collection.')
        return;
    }
    const
        collections_popup = document.querySelector('popup.collections'),
        book_collections = _.clone(window.book_collections)[book_id] || [];
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