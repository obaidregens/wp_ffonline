const popup_s = class {
    static get overlay () {
        let popup_overlay = DOM.q('popup_s-overlay');
        if (! popup_overlay){
            popup_overlay = document.createElement("popup_s-overlay");
            document.documentElement.appendChild(popup_overlay);            
        }
        return popup_overlay;
    }
    static create(_popup,{onClose,onOpen} = {}) {
        if (onClose){
            _popup.addEventListener('onClose',onClose);
        }
        if (onOpen){
            _popup.addEventListener('onOpen',onOpen);
        }
        if (! _popup.parentNode){
            document.documentElement.appendChild(_popup);
        }
    }
    static init () {
        const overlay = popup_s.overlay;
        overlay.addEventListener('click',popup_s.close);
        document.documentElement.addEventListener('click', popup_s.initTriggers);
    }
    static initTriggers (event) {
        if (event.target.classList.contains('popup_close')){
            popup_s.close();
            return;
        }
        const _popup = event.target.nextElementSibling;
        if (! event.target.classList.contains('popup_s') || _popup.tagName.toLowerCase() !== 'popup_s' ) {
            return;
        }
        popup_s.open(_popup);
    }
    static open (_popup) {
        setTimeout(() => {
            popup_s.close();
            const popup_overlay = popup_s.overlay;
            _popup.classList.add('show');
            popup_overlay.classList.add('show');
            document.documentElement.style.overflow = 'hidden';    
            _popup.dispatchEvent(new Event('onOpen'));    
        });
    }
    static close () {
        const popup_overlay = popup_s.overlay;
        const _popup = DOM.q('popup_s.show');
        if ( ! _popup ){
            return;
        }
        _popup.dispatchEvent(new Event('onClose'));
        _popup.classList.remove('show');
        if (DOM.qa('popup_s.show, next-screen.show, sidenav.show').length === 0){
            document.documentElement.style.overflow = 'auto';
        }
        if (DOM.qa('popup_s.show').length === 0){
            popup_overlay.classList.remove('show');
        }
        _popup.dispatchEvent(new Event('onAfterClose'));
    }
}
popup_s.init();