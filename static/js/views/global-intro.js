const getViewId = () => {
    const current_view_parts = window.location.pathname.split('/').filter((v) => v !== '');
    const current_full = current_view_parts.join('/');
    if (current_full === "") {
        return 1;
    }
    if (current_full === 'my-books') {
        return 2;
    }
    if (current_view_parts[0] === 'drafts') {
        switch (current_view_parts[2]) {
            case "edit":
                return 3;
            case "preview":
                return 4;
            default:
                return false;
        }
    }
    if (current_view_parts[0] === 'book') {
        switch (current_view_parts.length) {
            case 2:
                // Book
                return 5;
            case 3:
                // Chapter
                return 6;
            default:
                return false;
        }        
    }
    return false;
}
const introSteps = {
    1: [
        {
            intro: "Hi! We'll help you get started so you can quickly get to reading fanfiction.",
        },
        {
            element: 'filter-books > button',
            intro: 'You can filter fanfictions you want to read from here.',
        },
        {
            element: 'book > .dropdown',
            intro: "Like this book? Follow and share with others.",
        },
        {
            element: 'footer > nav > a[href="/contact"]',
            intro: 'Contact us if you have any queries/suggestions.'
        }
    ],
    2: [
        {
            element: 'main > a.button[label="Drafts"]',
            intro: "Create a draft to get started."
        }
    ],
    3: [
        {
            element: 'main > button[label="Export"]',
            intro: "Export your draft to other fanfiction sites."
        },
        {
            element: 'toolbar > [action="share"]',
            intro: "Collaborate on draft with others."
        },
        {
            element: 'editor',
            intro: 'Start writing :)'
        }
    ],
    4: [
        {
            element: 'button.edit-draft',
            intro: "Add your changes to draft."
        },
        {
            element: 'button.acs-button',
            intro: "Customize reading settings according to your preference."
        }

    ],
    5: [
        {
            element: 'input.collapsible',
            intro: "See all chapters this book has."
        },
        {
            element: '.book-collections',
            intro: "Add book to a collection."
        },
        {
            element: '.book-share',
            intro: "Share a book you like with others!."
        }
    ],
    6: [
        {
            element: '.acs-button',
            intro: "Customize reading settings according to your preference."
        },
        {
            element: '.search-button',
            intro: "Looking for something? Search entire book."
        },
        {
            element: 'book-options',
            intro: "Like this book? Follow and share with others."
        },
        {
            element: 'reviews',
            intro: "What do you think about this chapter? Leave a review for the author."
        },
    ],
}
function startIntro(){
    const viewID = getViewId();
    const visited = JSON.parse(cookies.all.intro || null) || [];
    if (visited.includes(viewID)){
        return;
    }
    visited.push(viewID);
    cookies.set("intro",JSON.stringify(visited));

    const steps = introSteps[viewID] || [];
    if (steps.length === 0) {
        return;
    }
    const intro = introJs();
    intro.setOptions({
        nextLabel: '',
        prevLabel: '',
        steps
    });
    intro.start();
}
window.onload = startIntro;