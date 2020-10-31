(() => {
    const getViewId = () => {
        const current_view_parts = window.location.pathname.split('/').filter((v) => v !== '');
        const current_full = current_view_parts.join('/');
        if (current_full === "read") {
            return 1;
        }
        if (logged_in && current_full === 'my-stories') {
            return 2;
        }
        if (current_view_parts[0] === 'my-stories' && current_view_parts.length > 1) {
            return 8;
        }
        if (current_view_parts[0] === 'drafts') {
            if (! current_view_parts[1]) {
                return 7;
            }
            switch (current_view_parts[2]) {
                case "edit":
                    return 3;
                case "preview":
                    return 4;
                default:
                    return false;
            }
        }
        if (current_view_parts[0] === 'story') {
            switch (current_view_parts.length) {
                case 2:
                    // Story
                    return 5;
                case 3:
                    // Chapter
                    return 6;
                default:
                    return 0;
            }        
        }
        return false;
    }
    const mobileLay = window.matchMedia("(max-width: 500px)").matches;
    const introSteps = {
        1: {v: 1,steps: [
            {
                intro: "Hi! We'll help you get started so you can quickly get to reading fanfiction.",
            },
            {
                element: 'dark-mode',
                intro: 'Easily switch to dark mode anytime.'
            },
            {
                element: 'filter-books > button',
                intro: 'Extensively filter fanfictions to find exactly what you\'re looking for.',
            },
            {
                element: 'book',
                intro: "Click on story to see more!",
            },
        ]},
        2: {v: 1,steps: [
            {
                element: 'a.button[label="Drafts"]',
                intro: "Click on drafts to start writing."
            },
            {
                element: 'a.button[label="Import Stories"]',
                intro: "Have stories on other sites? Easily import them to Fanfiction Online."
            },
            {
                element: 'a.new-book',
                intro: "Ready to publish your first story?"
            },
        ]},
        3: {v: 2,steps: [
            {
                element: 'button[label="Export"]',
                intro: "Export your draft to other fanfiction sites."
            },
            {
                element: 'toolbar > [action="share"]',
                intro: "Collaborate on draft with others."
            },
            {
                element: 'toolbar > [action="preview"]',
                intro: "Listen & preview draft."
            },
            {
                element: 'button[label="Publish"]',
                intro: "Publish draft as chapter."
            },
            {
                element: 'editor',
                intro: "Press CTRL + D while writing to find similar words."
            },
            {
                intro: 'Don\'t worry about saving. Your drafts auto-save as you write.'
            },
            {
                element: "save-time",
                intro: 'Click to compare and restore previous versions of draft.'
            },
            {
                intro: 'Start writing :)'
            }
        ]},
        4: {v: 2,steps: [
            {
                element: 'button.edit-draft',
                intro: "Add your changes to draft."
            },
            {
                intro: "To listen to your draft or customize reading, tap twice."
            },
            {
                intro: "To change the font, theme, or text size, tap twice."
            },
        ]},
        5: {v: 1,steps: [
            {
                element: 'input.collapsible',
                intro: "See all chapters this story has."
            },
            {
                element: '.book-collections',
                intro: "Add story to a collection."
            },
            {
                element: '.book-share',
                intro: "Share a story you like with others."
            },
            {
                element: '.book-share',
                intro: "Save offline to read without internet!"
            },
        ]},
        6: {v: 3,steps: [
            {
                intro: mobileLay ?
                "Search story, listen to chapter, customize reading, view chapters, or save offline to read without internet! Tap to open options." :
                "Search story, listen to chapter, customize reading, view chapters, or save offline to read without internet!",
                element: mobileLay ? null : 'acs-options'
            },
            {
                intro: "To change the font, theme, or text size, tap twice."
            },
            {
                element: '.book-vote',
                intro: "Enjoyed reading chapter? Vote for it!"
            },
            {
                element: 'book-options',
                intro: "Like the story? Follow and share with others."
            },
            {
                element: 'reviews-wrapper',
                intro: "What do you think about this chapter? Leave a review for the author."
            },
        ]},
        7: {v: 1,steps: [
            {
                element: 'floater > button.new',
                intro: 'Create a new draft.'
            }
        ]},
        8: {v: 1,steps: [
            {
                element: 'page:first-of-type',
                intro: 'Enter some basic details about your story.'
            },
            {
                element: 'step:nth-of-type(2)',
                intro: 'Tags to help readers find your story easily.'
            },
            {
                element: 'step:nth-of-type(3)',
                intro: 'Manage story settings and publish it.'
            },
            {
                element: 'step:nth-of-type(4)',
                intro: 'Add & edit chapter(s) to story.'
            },
        ]},
    }
    function startIntro(){
        setTimeout(async () => {
            const viewID = getViewId();
            if (viewID === false){
                return;
            }
            const visited = JSON.parse(await idbKeyval.get('intro')) || {};
            const lastUpdatedIntro = introSteps[viewID].v;
            if ( (visited[viewID] || 0) <= lastUpdatedIntro*-1 ){
                return;
            }
            visited[viewID] = lastUpdatedIntro*-1;
            await idbKeyval.set('intro',JSON.stringify(visited));
    
            const steps = introSteps[viewID].steps || [];
            if (steps.length === 0) {
                return;
            }
            const intro = introJs();
            intro.setOptions({
                nextLabel: '',
                prevLabel: '',
                exitOnOverlayClick: false,
                steps
            });
            intro.start();
        },500);
    }
    window.addEventListener('load',startIntro);
})();