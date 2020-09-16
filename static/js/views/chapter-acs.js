document.documentElement.appendChild(DOM.create('link',{
    attributes: {
        href: "https://fonts.googleapis.com/css2?family=Montserrat&family=Open+Sans&family=Pangolin&family=Merriweather&family=Raleway&family=Roboto&display=swap",
        rel: "stylesheet"
    }
}));
const acs_button = DOM.create('button',{
    classes: ['acs-button','popup']
});
document.documentElement.appendChild(acs_button);
const acs_popup = DOM.create('popup',{
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
            ]
        })
   ],
    listeners: {
        onOpen: () => window.acsSwipeEnabled = false,
        onAfterClose: () => window.acsSwipeEnabled = true
    }
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
    const chapter_content = document.querySelector('.acs-elem');
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
        _.prop(document.querySelector('.acs-button + popup change-options[action="' + acs_.key + '"] > button:last-child'), 'disabled', new_style === min_max_acs[acs_.key].max ? true : false);
        _.prop(document.querySelector('.acs-button + popup change-options[action="' + acs_.key + '"] > button:first-child'), 'disabled', new_style === min_max_acs[acs_.key].min ? true : false);
        chapter_content.style.setProperty('--' + acs_.key,new_style);
    }
}
document.querySelector('.acs-button + popup').addEventListener('click',function(event){
    const closest_change_options = event.target.closest('change-options');
    if (! closest_change_options) {
        return;
    }
    const action = closest_change_options.getAttribute('action');
    const chapter_content = document.querySelector('.acs-elem');
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
        themes.set(theme,{temp: ['peach'].includes(theme) });
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
document.querySelector('dark-mode').addEventListener('click',() => acs.set('theme',themes.current) );
window.addEventListener('load',() => {
    window.acsSwipeEnabled = true;
    const mc = new Hammer(document.documentElement);
    mc.on("panleft", event => {
        if (event.distance <= 70 || !window.acsSwipeEnabled) {
            return;
        }
        window.acsSwipeEnabled = false;
        popup.open(acs_popup);
    });
});