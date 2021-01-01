(() => {
    ['cut','copy'].forEach(eve => window.addEventListener(eve,event => {
        event.clipboardData.setData('text/plain', "");
        event.preventDefault();
        if(window.clipboardData){
            window.clipboardData.clearData();
        }
        return false;
    }));
    window.addEventListener('dragstart',event => {
        event.preventDefault();
        return false;
    });
    window.addEventListener('keydown',event => {
        if (_.cmd(event) && ["x",'c'].includes(event.key.toLowerCase()) ) {
            event.preventDefault();
            return false;    
        }
    });
    // Custom Menu
    const menu_instance = new menu(DOM.q('chapter > content'),[
        DOM.create('li',{
            classes: ["listen"],
            innerText: "Read Aloud",
            listeners: {
                mousedown: event => {
                    window.readFrom(_.childIndex(menu_instance.openedOn));
                }
            }
        }),
        DOM.create('li',{
            classes: ["quote"],
            innerText: "Quote",
            listeners: {
                mousedown: event => {
                    const reply_el = DOM.q('write-review > reply-to');
                    reply_el.setAttribute("hidden","0");
                    reply_el.setAttribute('review_id',"0");
                    const quote_el = DOM.q('write-review > quote');
                    quote_el.innerText = menu_instance.getCurrentSelection().trim();
                    _scroll.to(quote_el);
                }
            }
        })
    ]);
})();