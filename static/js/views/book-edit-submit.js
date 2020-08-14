function createChapterDraggableLi(chapter) {
    const topLevelAttr = {
        chapter_id: chapter.ID,
    };
    if (chapter.draft_id){
        topLevelAttr.draft_id = chapter.draft_id;
    }
    return DOM.create('li',{
        attributes: topLevelAttr,
        children: [
            DOM.create('cell',{
                classes: ['counter'],
            }),
            DOM.create('cell',{
                innerText: chapter.title,
            }),
            DOM.create('cell',{
                children: [
                    DOM.create('a',{
                        innerText: 'View',
                        attributes: {
                            target: '_blank',
                            href: chapter.view
                        }        
                    })
                ]
            }),
            DOM.create('cell',{
                children: [
                    DOM.create('a',{
                        innerText: 'Delete',
                        listeners: {
                            click: function() {
                                this.parentElement.parentElement.remove();
                            }
                        }
                    })
                ]
            }),
        ]
    });
}
function existing_chapters_open() {
    let p = document.querySelector('popup[existing_chapters]');
    if (p) {
        popup.open(p);
        return;
    }
    p = DOM.create('popup',{
        attributes: {
            existing_chapters: ''
        }
    });
    const chapters = [
        DOM.create('li',{
            attributes: {
                head: ''
            },
            children: [
                DOM.create('cell',{
                    innerText: '#'
                }),
                DOM.create('cell',{
                    innerText: 'Title'
                }),
                DOM.create('cell',{
                    innerText: 'View'
                }),
                DOM.create('cell',{
                    innerText: 'Delete'
                })
            ]
        })
    ];
    for (let i = 0; i < selected.chapters.length; i++) {
        const chapter = selected.chapters[i];
        chapters.push(createChapterDraggableLi({
            title: chapter.title,
            ID: chapter.ID,
            view: '/book/' + selected.book_id + '/' + chapter.num
        }));
    }
    const index = DOM.create('index',{
        children: chapters
    });
    p.appendChild(index);
    p.appendChild(DOM.create('button',{
        innerText: 'New Chapter',
        classes: ['new-chapter'],
        listeners: {
            click: new_chapter_popup
        }
    }));
    const sortable = new Draggable.Sortable(
        index, {
            draggable: 'li:not([head])',
            distance: 10
        }
    );
    popup.create(p);
    popup.open(p);
}
function new_chapter_popup() {
    let p = document.querySelector('popup[new_chapter]');
    if (p) {
        popup.open(p);
        return;
    }
    p = DOM.create('popup',{
        attributes: {
            new_chapter: ''
        }
    });
    const chapter_title = create_text_input({label: 'Chapter Title'});
    chapter_title.querySelector('input').setAttribute('maxlength','80');
    p.appendChild(chapter_title);
    const draft_frag = document.createDocumentFragment();
    for (let i = 0; i < tags.all.drafts.length; i++) {
        const draft = tags.all.drafts[i];
        draft_frag.appendChild(DOM.create('a',{
            classes: ['draft','grid-item'],
            attributes: {
                draft_id: draft.ID
            },
            listeners: {
                click: function() {
                    const chapter_title_input = chapter_title.querySelector('input');
                    const title = chapter_title_input.value;
                    if (title === '') {
                        new toast('What\'s the chapter title?');
                        return;
                    }
                    const draftID = this.getAttribute('draft_id');
                    const view = this.querySelector('a.preview').getAttribute('href');
                    document.querySelector('popup[existing_chapters] > index').appendChild(createChapterDraggableLi({
                        title,
                        ID: 'new',
                        view,
                        draft_id: draftID
                    }));
                    existing_chapters_open();
                    const existing_chapters_p = document.querySelector('popup[existing_chapters]');
                    existing_chapters_p.scrollTop = existing_chapters_p.scrollHeight;
                    chapter_title_input.value = '';
                }
            },
            children: [
                DOM.create('updated',{
                    innerText: draft.updated,
                }),
                DOM.create('draft-title',{
                    innerText: draft.title
                }),
                DOM.create('excerpt',{
                    innerText: draft.excerpt
                }),
                DOM.create('a',{
                    classes: ['preview'],
                    innerText: 'Preview',
                    attributes: {
                        href: '/drafts/' + draft.ID + '/preview'
                    }
                }),
            ]
        }));
    }
    p.appendChild(DOM.create('drafts',{
        classes: ['grid'],
        children: [draft_frag]
    }));
    p.appendChild(DOM.create('button',{
        attributes: {
            label: 'Cancel'
        },
        listeners: {
            click: existing_chapters_open
        }
    }));
    popup.create(p);
    popup.open(p);
}
document.querySelector('submit > [label="Chapters"]').addEventListener('click',() => {
    if (! selected.book_id || selected.book_id === 'new') {
        new toast('Save book first!');
        return;
    }
    existing_chapters_open();
});
document.querySelector('submit > [label="Save"]').addEventListener('click',({target}) => {
    for (let i = 0; i < selected.pairing.length; i++) {
        const pairing = selected.pairing[i];
        if (pairing.length < 2) {
            new toast('Pairing must have at least two characters.')
            return;
        }
    }
    target.setAttribute('disabled','');
    if (document.querySelector('popup[existing_chapters]')){
        const chapters = [];
        const chapter_el = document.querySelectorAll('popup[existing_chapters] > index > li:not([head])');
        for (let i = 0; i < chapter_el.length; i++) {
            const el = chapter_el[i];
            chapters.push({
                draft_id: el.getAttribute('draft_id'),
                ID: el.getAttribute('chapter_id'),
                title: el.children[1].innerText,
                num: i+1
            });
        }
        selected.chapters = chapters;    
    }
    api('edit_book',{
        dataType: 'JSON',
        data: selected,
        callback: response => {
            target.removeAttribute('disabled');
            if (response.code === 14) {
                new toast('Title is required');
                return;
            }
            if (response.code > 5) {
                new toast('An error occured.');
                return;
            }
            selected = response.selected;
            if (document.querySelector('popup[existing_chapters]')) {
                document.querySelector('popup[existing_chapters]').remove();
            }
            if (document.querySelector('popup[new_chapter]')) {
                document.querySelector('popup[new_chapter]').remove();
            }
            reRender();
            window.history.pushState("object or string", document.querySelector("title").innerText,'/my-books/' + selected.book_id);
            if (response.code === 1) {}
            else if (response.code === 2) {
                existing_chapters_open();
                new toast('Select a chapter to publish.');
            }
            else if (response.code === 3) {
                new toast('Book Title, Book Summary, Rating, Language and Status are required to publish book.')
            }
            new toast('Book Updated');
        }
    })
});
