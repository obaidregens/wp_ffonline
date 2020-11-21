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
        })
    ]);
})();