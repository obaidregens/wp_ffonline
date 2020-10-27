function create_collection_open(collection_id = 'new'){
    if (! logged_in ) {
        new toast('Login to create a collection');
        prompt_login();
        return;
    }
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
            const collections_o = _.clone(window.collections_data);
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
        api('update_collection',{
            data: {
                delete: to_delete,
                collection_id: cc_popup.getAttribute('collection_id') || 'new',
                book_ids: typeof OPT_BOOK_IN_COLLECTIONS === 'undefined' ? Object.keys(_.clone(window.book_collections)) : [],
                title: cc_popup.querySelector('text-input > input').value,
                privacy: privacySelect.value
            }
        })
        .then(response => {
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
                if (typeof OPT_BOOK_IN_COLLECTIONS === 'undefined') {
                    update_collections(response.collections_data);
                    window.book_collections = _.clone(response.book_collections);
                }
                else {
                    window.location.href = '/@me/collections';
                }
                window.collections_data = _.clone(response.collections_data);    
            }
            else {
                new toast('An error occured');
            }
            if (typeof OPT_BOOK_IN_COLLECTIONS === 'undefined') {
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