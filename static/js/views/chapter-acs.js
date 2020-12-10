document.documentElement.appendChild(DOM.create('link',{
    attributes: {
        href: "https://fonts.googleapis.com/css2?family=Montserrat&family=Open+Sans&family=Pangolin&family=Merriweather&family=Raleway&family=Roboto&display=swap",
        rel: "stylesheet"
    }
}));
(() => {
    const cex = typeof chapter_id !== 'undefined';
    const htmlEl = DOM.q('html');
    if (cex) {
        const acs_top = htmlEl.appendChild(DOM.create('acs-options',{
            children: [
                DOM.create('button',{
                    classes: ['search'],
                    listeners: {
                        click: () => {
                            htmlEl.classList.remove('show-acs');
                            popup.open(DOM.q('popup.search-story'));
                        }
                    }
                }),
                DOM.create('button',{
                    classes: ['offline'],
                    listeners: {
                        click: () => {
                            htmlEl.classList.remove('show-acs');
                        }
                    }
                }),
            ],
        }));
    }
    const acs_bottom = htmlEl.appendChild(DOM.create('acs-options',{
        children: [
            DOM.create('button',{
                classes: ['themes'],
                listeners: {
                    click: () => {
                        htmlEl.classList.remove('show-acs');
                        popup.open(acs_popup);
                    }
                }
            }),
            DOM.create('button',{
                classes: ['text'],
                listeners: {
                    click: () => {
                        htmlEl.classList.remove('show-acs');
                        popup.open(acs_popup);
                    }
                }
            }),
            !cex ? null : DOM.create('button',{
                classes: ['collections'],
                listeners: {
                    click: collections.open.bind(null,book_id)
                }
            }),
            !cex ? null : DOM.create('button',{
                classes: ['index'],
                listeners: {
                    click: () => {
                        htmlEl.classList.remove('show-acs');
                        popup.open(DOM.q('popup.chapter-index'));
                    }
                }
            }),
            !cex ? null : DOM.create('button',{
                classes: ['reviews'],
                listeners: {
                    click: () => {
                        DOM.q('reviews-wrapper').scrollIntoView();
                        document.documentElement.scrollTop = document.documentElement.scrollTop - 60;
                    }
                }
            }),
        ]
    }));
    let timeLastClicked = Date.now();
    DOM.q('main').addEventListener('click',() => {
        if ( (Date.now() - timeLastClicked) < 300 ) {
            htmlEl.classList.remove('show-acs');
            popup.open(acs_popup);
            return;
        }
        timeLastClicked = Date.now();
        htmlEl.classList.toggle('show-acs');
    });    
})();
const acs_popup = DOM.create('popup',{
    classes: ['acs-popup'],
   children: [
       DOM.create('change-options',{
           attributes: {
               action: 'font'
           },
           children: [
               DOM.create('button',{
                   classes: ['dropdown'],
                   attributes: {
                       theme: ''
                   },
                   children: [
                       document.createTextNode('Font'),
                       DOM.create('dropdown',{
                           classes: ['right'],
                           children: [
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               }),
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               }),
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               }),
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               }),
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               }),
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               }),
                               DOM.create('li',{
                                   attributes: {
                                       tabindex: "0"
                                   }
                               })
                           ]
                       })
                   ]
               })
           ]
       }),
       DOM.create('change-options',{
            attributes: {
                action: 'fontSize'
            },
            children: [
                document.createElement('button'),
                document.createElement('button')
            ]
        }),
        DOM.create('change-options',{
            attributes: {
                action: 'lineHeight'
            },
            children: [
                document.createElement('button'),
                document.createElement('button')
            ]
        }),
        DOM.create('change-options',{
            attributes: {
                action: 'paragraphHeight'
            },
            children: [
                document.createElement('button'),
                document.createElement('button')
            ]
        }),
        DOM.create('change-options',{
            attributes: {
                action: 'width'
            },
            children: [
                document.createElement('button'),
                document.createElement('button')
            ]
        }),
        DOM.create('change-options',{
            attributes: {
                action: 'theme'
            },
            children: [
                DOM.create('div',{
                    attributes: {
                        color: 'light'
                    }
                }),
                DOM.create('div',{
                    attributes: {
                        color: 'dark'
                    }
                }),
                DOM.create('div',{
                    attributes: {
                        color: 'peach'
                    }
                }),
                DOM.create('div',{
                    attributes: {
                        color: 'black'
                    }
                }),
            ]
        })
   ],
});
popup.create(acs_popup);

const min_max_acs = {
    fontSize: {
        min: 5,
        max: 15
    },
    lineHeight: {
        min: 2,
        max: 10
    },
    paragraphHeight: {
        min: 0,
        max: 10
    },
    width: {
        min: 3,
        max: 10
    },
};
const acs_entries = Object.entries(acs.all);
for (let i = 0; i < acs_entries.length; i++) {
    const chapter_content = DOM.q('.acs-elem');
    const acs_ = {
        key: acs_entries[i][0],
        value: acs_entries[i][1]
    }
    if (acs_.key === 'font') {
        chapter_content.style.setProperty('font-family',acs_.value);
    }
    else if (acs_.key === 'theme') {
        themes.set(acs_.value,{temp: true});
    }
    else if (['fontSize','lineHeight','paragraphHeight','width'].includes(acs_.key)){
        const new_style = parseInt(acs_.value);
        _.prop(DOM.q('.acs-popup change-options[action="' + acs_.key + '"] > button:last-child'), 'disabled', new_style === min_max_acs[acs_.key].max ? true : false);
        _.prop(DOM.q('.acs-popup change-options[action="' + acs_.key + '"] > button:first-child'), 'disabled', new_style === min_max_acs[acs_.key].min ? true : false);
        chapter_content.style.setProperty('--' + acs_.key,new_style);
    }
}
DOM.q('.acs-popup').addEventListener('click',function(event){
    const closest_change_options = event.target.closest('change-options');
    if (! closest_change_options) {
        return;
    }
    const action = closest_change_options.getAttribute('action');
    const chapter_content = DOM.q('.acs-elem');
    if (
        ['fontSize','lineHeight','paragraphHeight','width'].includes(action) &&
        event.target.tagName.toLowerCase() === 'button'
    ){
        const arithmetic = event.target.nextElementSibling ? -1 : 1;
        const current_style = parseInt(getComputedStyle(chapter_content).getPropertyValue('--' + action));
        const new_style = current_style + arithmetic;
        const plus_btn = this.querySelector('change-options[action="' + action + '"] > button:last-child');
        const minus_btn = this.querySelector('change-options[action="' + action + '"] > button:first-child');
        _.prop(plus_btn, 'disabled', new_style === min_max_acs[action].max ? true : false);
        _.prop(minus_btn, 'disabled', new_style === min_max_acs[action].min ? true : false);
        if (new_style > min_max_acs[action].max || new_style < min_max_acs[action].min) {
            return;
        }
        chapter_content.style.setProperty('--' + action,new_style);
        acs.set(action,new_style);
    }
    else if (action === 'theme'){
        const theme = event.target.getAttribute('color');
        acs.set('theme',theme);
        themes.set(theme,{temp: !['dark','light'].includes(theme) });
    }
    else if (action === 'font') {
        if (event.target.parentElement.tagName.toLowerCase() !== 'dropdown'){
            return;
        }
        const fontName = getComputedStyle(event.target,':before').getPropertyValue('font-family');
        chapter_content.style.setProperty('font-family',fontName);
        acs.set('font',fontName);
    }
});
DOM.q('dark-mode').remove();