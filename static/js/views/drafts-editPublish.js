// Publish Button
document.querySelector('button[label="Publish"]').addEventListener('click', ({target}) => {
    let pop = document.querySelector('popup[publish_to_story]');
    if (! pop){
        pop = DOM.create('popup',{
            attributes: {
                publish_to_story: ""
            },
            children: [
                DOM.create('h4',{
                    innerText: 'Publish to story'
                }),
                create_text_input({
                    label: 'Chapter Title',
                    attributes: {
                        maxlength: "80"
                    },
                }),
                DOM.create('input',{
                    classes: ['collapsible'],
                    attributes: {
                        type: 'checkbox'
                    },
                }),
                DOM.create('label',{
                    innerText: "Author Notes"
                }),
                DOM.create('collapsible',{
                    children: [
                        create_text_input({label: "Start of Chapter",type: 'textarea'}),
                        create_text_input({label: "End of Chapter",type: 'textarea'}),
                    ]
                }),
                DOM.create('stories-list')
            ]
        });
        popup.create(pop);    
    }
    api('get_stories').then(response => {
        const story_pop = document.querySelector('popup[publish_to_story] > stories-list');
        story_pop.innerText = "";
        for (let i = 0; i < response.stories.length; i++) {
            const story = response.stories[i];
            story_pop.appendChild(DOM.create('a',{
                classes: ['story'],
                innerText: story.title,
                attributes: story,
                listeners: {
                    click: ({target}) => {
                        const draft_id = document.querySelector('editor').getAttribute('draft_id');
                        const chapter_title = document.querySelector('popup[publish_to_story] > text-input > input').value;
                        if (draft_id === 'new'){
                            new toast('This draft hasn\'t been saved.');
                            return;
                        }
                        if (chapter_title === ''){
                            new toast('What should the chapter title be?');
                            return;
                        }
                        confirmation(`Are you sure you want to publish the new chapter "${chapter_title}"?`).then(v => {
                            if (! v) {
                                return;
                            }
                            popup.close();
                            api('publish_to_story',{
                                data: {
                                    chapter_title,
                                    draft_id,
                                    storyID: story.ID,
                                    preAN: pop.querySelector('text-input:nth-of-type(1) > textarea').value,
                                    postAN: pop.querySelector('text-input:nth-of-type(2) > textarea').value,
                                }
                            }).then(response => {
                                if (response.code > 5) {
                                    new toast('An error occured');
                                    return;
                                }
                                new toast('Published!');
                                window.location.href = response.new_chapter_link;
                            });
                        });
                    }
                }
            }));
        }
        if (response.stories.length === 0) {
            story_pop.appendChild(DOM.create('span',{
                innerHTML: "No stories yet. <a href='/my-stories/new'>Create story</a>"
            }));
        }
    });
    popup.open(pop);
});