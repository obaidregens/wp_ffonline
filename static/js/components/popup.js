const popup = class {
    static get overlay () {
        let popup_overlay = DOM.q('popup-overlay');
        if (! popup_overlay){
            popup_overlay = document.createElement("popup-overlay");
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
        const overlay = popup.overlay;
        overlay.addEventListener('click',popup.close);
        document.documentElement.addEventListener('click', popup.initTriggers);
    }
    static initTriggers (event) {
        if (event.target.classList.contains('popup_close')){
            popup.close();
            return;
        }
        const _popup = event.target.nextElementSibling;
        if (! event.target.classList.contains('popup') || _popup.tagName.toLowerCase() !== 'popup' ) {
            return;
        }
        popup.open(_popup);
    }
    static open (_popup) {
        setTimeout(() => {
            popup.close();
            const popup_overlay = popup.overlay;
            _popup.classList.add('show');
            popup_overlay.classList.add('show');
            document.documentElement.style.overflow = 'hidden';    
            _popup.dispatchEvent(new Event('onOpen'));    
        });
    }
    static close () {
        const popup_overlay = popup.overlay;
        const _popup = DOM.q('popup.show');
        if ( ! _popup ){
            return;
        }
        _popup.dispatchEvent(new Event('onClose'));
        _popup.classList.remove('show');
        if (DOM.qa('popup.show, next-screen.show, sidenav.show').length === 0){
            document.documentElement.style.overflow = 'auto';
        }
        if (DOM.qa('popup.show').length === 0){
            popup_overlay.classList.remove('show');
        }
        _popup.dispatchEvent(new Event('onAfterClose'));
    }
}
popup.init();