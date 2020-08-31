const OPT_REMOVE_FILES_CLICK = true;
const OPT_NEW_DRAFT_IN_NEW_TAB = true;
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
    if (document.querySelector('page.active')) {
        document.querySelector('page.active').classList.remove('active');
    }
    if (document.querySelector('stepper > step.active')) {
        document.querySelector('stepper > step.active').classList.remove('active');
    }
    document.querySelector('app > page:nth-child(' + currentPage + '), page[page-num="' + currentPage + '"]').classList.add('active');
    document.querySelector('stepper > step:nth-child(' + currentPage + ')').classList.add('active');
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
                    innerText: 'View'
                }),
                DOM.create('cell',{
                    innerText: 'Delete'
                })
            ]
        })
    ];
    for (let i = 0; i < (selected.chapters || []).length; i++) {
        const chapter = selected.chapters[i];
        chapters.push(createChapterDraggableLi({
            title: chapter.title,
            ID: chapter.ID,
            view: '/story/' + selected.book_id + '/' + chapter.num
        }));
    }
    const index = DOM.create('index',{
        children: chapters
    });
    const sortable = new Draggable.Sortable(
        index, {
            draggable: 'li:not([head])',
            distance: 10
        }
    );
    document.querySelector('page[page-num="4"]').innerText = '';
    document.querySelector('page[page-num="4"]').appendChild(index);
    document.querySelector('page[page-num="4"]').appendChild(DOM.create('button',{
        classes: ['new-chapter'],
        innerText: 'New Chapter',
        listeners: {
            click: () => popup.open(document.querySelector('popup[new_chapter]'))
        }
    }));
}
window.addEventListener('load',() => {
    document.querySelector('stepper').addEventListener('click',({target}) => {
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
function new_chapter_popup_create() {
    const p = DOM.create('popup',{
        attributes: {
            new_chapter: ''
        }
    });
    const chapter_title = create_text_input({label: 'Chapter Title'});
    const chapter_title_input = chapter_title.querySelector('input');
    chapter_title_input.setAttribute('maxlength','80');
    p.appendChild(chapter_title);
    p.appendChild(DOM.create('folder-listing',{
        listeners: {
            click: ({target}) => {
                let file = null;
                if (target.tagName.toLowerCase() === 'file') {
                    file = target;
                }
                if (target.parentElement.tagName.toLowerCase() === 'file') {
                    file = target.parentElement;
                }
                if (file === null) {
                    return;
                }
                const title = chapter_title_input.value;
                if (title === '') {
                    new toast('What\'s the chapter title?');
                    return;
                }
                const draft_id = file.getAttribute('draft_id');
                document.querySelector('page[page-num="4"] > index').appendChild(createChapterDraggableLi({
                    title,
                    ID: 'new',
                    draft_id,
                    view: '/drafts/' + draft_id + '/preview'
                }));
                popup.close();
            }
        }
    }));
    popup.create(p);
}
new_chapter_popup_create();
document.querySelector('submit > [label="Save"]').addEventListener('click',({target}) => {
    for (let i = 0; i < selected.pairing.length; i++) {
        const pairing = selected.pairing[i];
        if (pairing.length < 2) {
            new toast('Pairing must have at least two characters.')
            return;
        }
    }
    target.setAttribute('disabled','');
    const chapters = [];
    const chapter_el = document.querySelectorAll('page[page-num="4"] > index > li:not([head])');
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
            prev_status = selected.publish;
            selected = response.selected;
            if (document.querySelector('popup[new_chapter]')) {
                document.querySelector('popup[new_chapter]').remove();
                new_chapter_popup_create();
            }
            reAddChapters();
            reRender();
            window.history.pushState("object or string", document.querySelector("title").innerText,'/my-stories/' + selected.book_id);
            if (response.code === 1) {}
            else if (response.code === 2) {
                window.setCurrentPage(4);
                new toast('Select a chapter to publish.');
            }
            else if (response.code === 3) {
                new toast('Story Title, Story Summary, Rating, Language and Status are required to publish story.')
            }
            new toast('Story Updated');
            const newPublishNotice = document.querySelector('exciting');
            if (prev_status === false && selected.publish === true) {
                newPublishNotice.innerText = '';
                const shareData = {
                    title: selected.title,
                    href: '/story/' + selected.book_id,
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
                            href: shareData.href
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
            }
        }
    })
});