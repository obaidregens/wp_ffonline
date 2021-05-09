// Shortcuts Popup
(() => {
    DOM.q('infobar').prepend(DOM.create('a',{
        innerText: "Shortcuts",
        listeners: {
            click: () => {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                }        
                popup_s.open(shortcut_popup);
            }
        }
    }));
    const shortcut_popup = document.documentElement.appendChild(DOM.create('popup_s',{
        attributes: {
            shortcuts: ""
        },
        children: [
            DOM.create('index',{
                children: [DOM.create('li',{
                    attributes: {
                        head: ""
                    },
                    children: [
                        DOM.create('cell',{
                            innerText: "Actions"
                        }),
                        DOM.create('cell',{
                            innerText: "Mac"
                        }),
                        DOM.create('cell',{
                            innerText: "Windows"
                        }),
                    ]
                })].concat(Object.entries({
                    "<strong>Bold</strong>": "b",
                    "<em>Italic</em>": "i",
                    "Center": "e",
                    "Similar Words": "d",
                    "Older Version": "↑",
                    "Newer Version": "↓",
                }).map(([tool,shortcut]) => DOM.create('li',{
                    children: [
                        DOM.create('cell',{
                            innerHTML: tool
                        }),
                        DOM.create('cell',{
                            innerText: "⌘ + " + shortcut
                        }),
                        DOM.create('cell',{
                            innerText: "Ctrl + " + shortcut
                        }),
                    ]
                })))        
            })
        ]
    }))
})();