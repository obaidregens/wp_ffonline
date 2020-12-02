const collections = class {
    static get open_popup () {
        let cp = DOM.q('popup.collections');
        if (cp) {
            return cp;
        }
        return document.documentElement.appendChild(DOM.create("popup",{
            classes: ['collections'],
            children: Object.values(collections.collections_data).map(collection => DOM.create('collection',{
                children: [
                    DOM.create('label',{
                        classes: ['switch'],
                        children: [
                            DOM.create('input',{
                                attributes: {
                                    type: 'checkbox',
                                    label: collection.title + " (" + collection.type + ")",
                                    collection_id: collection.ID
                                }
                            }),
                            DOM.create('text',{
                                innerText: collection.title + " (" + collection.type + ")"
                            })
                        ]
                    }),
                    DOM.create('collection-options',{
                        children: [
                            DOM.create('a',{
                                attributes: {
                                    href: collection.link,
                                    "tooltip-top": "View",
                                    target: "_blank"
                                }
                            }),
                            DOM.create('a',{
                                attributes: {
                                    "tooltip-top":  "Edit",
                                    "disabled": ['Favorites','Hidden'].includes(collection.title) ? "" : null
                                },
                                listeners: {
                                    click: ({target}) => collections.edit(collection.ID,target.closest('popup').getAttribute('book_id'))
                                }
                            })
                        ]
                    })
                ]
            })).concat([
                DOM.create('a',{
                    classes: ["new-collection"],
                    innerText: "Create Collection",
                    listeners: {
                        click: ({target}) => {
                            collections.edit('new',target.closest('popup').getAttribute('book_id'))
                        }
                    }
                })
            ]),
            listeners: {
                change: ({target}) => {
                    if (! target.tagName.toLowerCase() === 'input'){
                        return;
                    }
                    collections.api_add(
                        target.getAttribute('collection_id'),
                        target.closest('popup').getAttribute('book_id'),
                        target.checked
                    );
                }
            }
        }))
    }
    static get edit_popup () {
        let cp = DOM.q('popup[update_collection]');
        if (cp) {
            return cp;
        }
        return document.documentElement.appendChild(DOM.create("popup",{
            attributes: {
                update_collection: "",
            },
            children: [
                create_text_input({
                    label: "Collection Name"
                }),
                DOM.create('select',{
                    children: ['Public','Unlisted','Private'].map(prv => DOM.create('option',{
                        innerText: prv,
                        attributes: {
                            value: prv
                        }
                    }))
                }),
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
                        click: collections.submit
                    }
                }),
                DOM.create('button',{
                    attributes: {
                        label: 'Delete',
                        theme: ''
                    },
                    listeners: {
                        click: collections.submit
                    }
                }),
                DOM.create('a',{
                    classes: ['back'],
                    listeners: {
                        click: ({target}) => {
                            const prev_book_id = target.closest('popup').getAttribute('prev_book_id');
                            if (prev_book_id === 0) {
                                popup.close();
                                return;
                            } 
                            collections.open(prev_book_id);
                        }
                    }
                })
            ]
        }))
    }
    static async api_add (collection_id,book_id,add) {
        const {code,book_collections,is_hidden} = await api('add_to_collection',{
            data: {
                collection_id,
                book_id,
                add
            }
        });
        if (code > 5) {
            new toast("An error occured");
            return;
        }
        if (is_hidden && add) {
            new toast("Story will be hidden in your next search.");
        }
        (collections.book_collections || {})[book_id] = book_collections[book_id];
        collections.open(book_id);
    }
    static open(book_id) {
        if (!logged_in) {
            new toast("Login to add to collection");
            return prompt_login();
        }
        const pop_c = collections.open_popup;
        pop_c.setAttribute('book_id',book_id);
        pop_c.querySelectorAll('label.switch > input').forEach(el => {
            el.checked = collections.book_collections[book_id].includes(el.getAttribute('collection_id'));
        } );
        popup.open(pop_c);
    }
    static edit(id,prev_book_id = 0) {
        if (!logged_in) {
            new toast("Login to create collection");
            return prompt_login();
        }
        const pop_c = collections.edit_popup;
        pop_c.setAttribute('collection_id',id);
        pop_c.setAttribute('prev_book_id',prev_book_id);
        if (id !== "new") {
            pop_c.querySelector('text-input > input').value = collections.collections_data[id].title;
            pop_c.querySelector('text-input > input').dispatchEvent(new Event('change'));
            pop_c.querySelector('select').value = collections.collections_data[id].type;
        }
        popup.open(pop_c);
    }
    static async submit ({target}) {
        const cc_popup = target.closest('popup');
        const to_delete = target.getAttribute('label') === 'Delete';
        const collection_id = cc_popup.getAttribute('collection_id') || 'new';
        const title = cc_popup.querySelector('text-input > input').value;
        const privacy = cc_popup.querySelector('select').value;
        const {code,notice,collections_data} = await api('update_collection',{
            data: {
                to_delete,
                collection_id: collection_id,
                title,
                privacy,
            }
        });
        if (code > 5) {
            new toast("An error occured");
            return;
        }
        new toast(notice);
        if (DOM.q('popup.collections')) {
            DOM.q('popup.collections').remove();
        }
        collections.collections_data = collections_data;
        const ur = window.location.pathname.split('/').filter(v => v !== "");
        if (ur.length === 1 && ur[0] === "collections") {
            // Collections
            window.location.href = "/@me/collections";
            return;
        }
        if (ur.length === 2 && ur[1] === 'collections'){
            // Author Collections
            window.location.reload();
            return;
        }
        if (ur.length === 2 && ur[0] === "collections") {
            // Collections
            if (to_delete) {
                window.location.href = "/@me/collections";
                return;
            }
            window.location.reload();
        }
        const prev_book_id = target.closest('popup').getAttribute('prev_book_id');
        if (parseInt(prev_book_id) === 0) {
            popup.close();
            return;
        }
        collections.open(prev_book_id);
    }
};
window.expose = window.expose || {};
window.expose.collections_edit = collections.edit;