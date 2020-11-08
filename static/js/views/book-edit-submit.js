let currentPage = null;
window.setCurrentPage = num => {
    if (num === '+') {
        currentPage++;
        return;
    }
    if (num === '-') {
        currentPage--;
        return;
    }
    currentPage = num;
    if (DOM.q('page.active')) {
        DOM.q('page.active').classList.remove('active');
    }
    if (DOM.q('stepper > step.active')) {
        DOM.q('stepper > step.active').classList.remove('active');
    }
    DOM.q('app > page:nth-child(' + currentPage + '), page[page-num="' + currentPage + '"]').classList.add('active');
    DOM.q('stepper > step:nth-child(' + currentPage + ')').classList.add('active');
}
function reAddChapters() {
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
                    innerText: 'Edit'
                }),
                DOM.create('cell',{
                    innerText: 'View'
                }),
                DOM.create('cell',{
                    innerText: 'Delete'
                }),
            ],
        })
    ];
    for (let i = 0; i < (selected.chapters || []).length; i++) {
        const chapter = selected.chapters[i];
        chapters.push(createChapterDraggableLi({
            title: chapter.title,
            ID: chapter.ID,
            view: '/story/' + selected.book_id + '/' + chapter.num,
            preAN: chapter.preAN,
            postAN: chapter.postAN,
        }));
    }
    const index = DOM.create('index',{
        children: chapters
    });
    if (!selected.updating) {
        const sortable = new Draggable.Sortable(index, {
            draggable: 'li:not([head])',
            distance: 10
        });    
    }
    const chapPage = DOM.q('page[page-num="4"]');
    chapPage.innerText = '';
    if (selected.updating) {
        chapPage.appendChild(DOM.create('important',{
            innerHTML: "You can't add new chapters because this story is being auto updated. You can though, edit your existing chapters. To add new chapters, <a href='/import-stories'>disable auto update</a>."
        }));
    }
    chapPage.appendChild(index);
    chapPage.appendChild(DOM.create('button',{
        classes: ['new-chapter'],
        attributes: {
            disabled: selected.updating ? "" : null
        },
        innerText: 'New Chapter',
        listeners: {
            click: () => {
                const pnc = DOM.q('popup[new_chapter]');
                pnc.querySelectorAll('textarea, input').forEach(el => {
                    el.value = "";
                    el.dispatchEvent(new Event('change'));
                    el.dispatchEvent(new Event('input'));
                });
                popup.open(pnc);
            }
        }
    }));
}
window.addEventListener('load',async () => {
    await window.tt;
    DOM.q('stepper').addEventListener('click',({target}) => {
        if (target.tagName.toLowerCase() !== 'step') {
            return;
        }
        const i = Array.prototype.indexOf.call(target.parentElement.children, target)+1;
        window.setCurrentPage(i);
    });
    reAddChapters();
    window.setCurrentPage(1);    
});
function createChapterDraggableLi(chapter) {
    const topLevelAttr = {
        chapter_id: chapter.ID,
        "pre-an": chapter.preAN || "",
        "post-an": chapter.postAN || "",
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
                classes: ['break'],
                innerText: chapter.title,
            }),
            DOM.create('cell',{
                children: [
                    DOM.create('a',{
                        innerText: 'Edit',
                        listeners: {
                            click: ({target}) => {
                                const pnc = DOM.q('popup[new_chapter]');

                                const row = target.parentElement.parentElement;
                                pnc.querySelector('text-input > input').value = row.querySelector('cell:nth-child(2)').innerText;
                                pnc.querySelector('collapsible > text-input:nth-child(1) > textarea').value = row.getAttribute('pre-an');
                                pnc.querySelector('collapsible > text-input:nth-child(2) > textarea').value = row.getAttribute('post-an');

                                pnc.querySelectorAll('textarea, input').forEach(el => {
                                    el.dispatchEvent(new Event('change'));
                                    el.dispatchEvent(new Event('input'));
                                } );
                                pnc.setAttribute('edit-chapter',chapter.ID);
                                popup.open(pnc);
                            }
                        }
                    })
                ]
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
                        attributes: {
                            disabled: selected.updating ? "" : null 
                        },
                        listeners: {
                            click: ({target}) => target.parentElement.parentElement.remove()
                        }
                    })
                ]
            }),
        ]
    });
}
function new_chapter_popup_create() {
    const p = DOM.create('popup',{
        attributes: {
            new_chapter: ''
        },
        listeners: {
            onAfterClose: () => {
                const chapter_id = p.getAttribute('edit-chapter');
                if (chapter_id !== null) {
                    const row = DOM.q(`page > index > li[chapter_id="${chapter_id}"]`);
                    row.querySelector('cell:nth-child(2)').innerText = p.querySelector('text-input > input').value;
                    row.setAttribute('pre-an',p.querySelector('collapsible > text-input:nth-child(1) > textarea').value);
                    row.setAttribute('post-an',p.querySelector('collapsible > text-input:nth-child(2) > textarea').value);
                }
                p.removeAttribute('edit-chapter');
            }
        }
    });
    const chapter_title = create_text_input({label: 'Chapter Title'});
    const chapter_title_input = chapter_title.querySelector('input');
    chapter_title_input.setAttribute('maxlength','80');
    p.appendChild(chapter_title);
    p.appendChild(DOM.create('input',{
        classes: ['collapsible'],
        attributes: {
            type: 'checkbox'
        },
    }));
    p.appendChild(DOM.create('label',{
        innerText: "Author Notes"
    }));
    const preAN = create_text_input({label: "Start of Chapter",type: 'textarea'});
    const postAN = create_text_input({label: "End of Chapter",type: 'textarea'});
    p.appendChild(DOM.create('collapsible',{
        children: [
            p.appendChild(preAN),
            p.appendChild(postAN),
        ]
    }));
    p.appendChild(DOM.create('a',{
        classes: ['edit-as-new'],
        listeners: {
            click: async () => {
                const res = await api('edit_chapter',{data: {chapter_id: p.getAttribute('edit-chapter')}});
                if (res.code > 7) {
                    new toast("An error occured");
                    return;
                }
                window.location.href = "/drafts/" + res.draft_id + "/edit"
            }
        }
    }));
    p.appendChild(DOM.create('folder-listing',{
        listeners: {
            click: ({target}) => {
                // Find File
                let file = null;
                if (target.tagName.toLowerCase() === 'file') {
                    file = target;
                }
                if (target.parentElement.tagName.toLowerCase() === 'file') {
                    file = target.parentElement;
                }
                if (file === null || file.classList.contains('new-draft-file') ) {
                    return;
                }
                const words = file.getAttribute('words');
                if (parseInt(words) < 10) {
                    new toast("Chapter has to be of at least 10 words!");
                    return;
                }
                // Title
                const title = chapter_title_input.value;
                if (title === '') {
                    new toast('What\'s the chapter title?');
                    return;
                }
                const draft_id = file.getAttribute('draft_id');
                const chapter_id = p.getAttribute('edit-chapter');
                const Li = createChapterDraggableLi({
                    title,
                    ID: chapter_id !== null ? chapter_id : 'new',
                    draft_id,
                    view: '/drafts/' + draft_id + '/preview',
                    preAN: preAN.querySelector('textarea').value,
                    postAN: postAN.querySelector('textarea').value,
                });
                if ( chapter_id === null ) {
                    DOM.q('page[page-num="4"] > index').appendChild(Li);
                }
                else {
                    DOM.q(`page[page-num="4"] > index > li[chapter_id="${chapter_id}"]`).replaceWith(Li);
                }
                popup.close();
            }
        }
    }));
    popup.create(p);
    rootDraftsIndex('folder-listing',{OPT_REMOVE_FILES_CLICK: true,OPT_NEW_DRAFT_IN_NEW_TAB: true, WITH_WORDS: true});
}
new_chapter_popup_create();
DOM.q('submit > [label="Save"]').addEventListener('click',({target}) => {
    for (let i = 0; i < selected.pairing.length; i++) {
        const pairing = selected.pairing[i];
        if (pairing.length < 2) {
            new toast('Pairing must have at least two characters.')
            return;
        }
    }
    target.setAttribute('disabled','');
    const chapters = [];
    const chapter_el = DOM.qa('page[page-num="4"] > index > li:not([head])');
    for (let i = 0; i < chapter_el.length; i++) {
        const el = chapter_el[i];
        const title = el.children[1].innerText;
        if (title.trim() === "") {
            new toast("Chapter title cannot be empty.");
            return;
        }
        chapters.push({
            draft_id: el.getAttribute('draft_id'),
            ID: el.getAttribute('chapter_id'),
            title,
            num: i+1,
            preAN: el.getAttribute("pre-an"),
            postAN: el.getAttribute("post-an"),
        });
    }
    selected.chapters = chapters;
    api('edit_book',{
        data: selected
    })
    .then(response => {
        target.removeAttribute('disabled');
        response.errors.forEach(er => new toast(er,3000));
        if (!response.selected) {
            return;
        }
        selected = response.selected;
        if (DOM.q('popup[new_chapter]')) {
            DOM.q('popup[new_chapter]').remove();
            new_chapter_popup_create();
        }
        reAddChapters();
        reRender();
        window.history.pushState("object or string", DOM.q("title").innerText,'/my-stories/' + selected.book_id);
        
        if (!response.new_publish) {
            new toast('Story Updated',3000);
            if (response.errors.length > 0) {
                new toast("Your story couldn't be published",3000,["warning"]);
            }
            return;
        }
        new toast("Story Published!");
        const newPublishNotice = DOM.q('exciting');
        newPublishNotice.innerText = '';
        const shareData = {
            title: selected.title,
            share_title: `I found a great fanfiction by ${selected.username} '${selected.title}'!`,
            url: 'https://fanfiction.online/story/' + selected.book_id,
            author: selected.username,
            desc: selected.description,
            callOnCopy: () => new toast('Copied!')
        };
        DOM.append(newPublishNotice,[
            DOM.create('span',{
                innerText: 'Your story has been published!'
            }),
            DOM.create('br'),
            DOM.create('span',{
                innerText: 'While it may take some time to appear in searches, you can still '
            }),
            DOM.create('a',{
                innerText: 'read',
                attributes: {
                    target: '_blank',
                    href: shareData.url
                }
            }),
            DOM.create('span',{
                innerText: ' & '
            }),
            DOM.create('a',{
                innerText: 'share',
                listeners: {
                    click: () => shareAPI(shareData)
                }
            }),
            DOM.create('span',{
                innerText: ' it with your friends.'
            })
        ]);
    });
});