const popup = class {
    static get overlay () {
        let popup_overlay = document.querySelector('popup-overlay');
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
        document.documentElement.addEventListener('click', popup.initTriggers);
        overlay.addEventListener('click',popup.close);
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
        const popup_overlay = popup.overlay;
        _popup.classList.add('show');
        popup_overlay.classList.add('show');
        document.documentElement.style.overflow = 'hidden';    
        _popup.dispatchEvent(new Event('onOpen'));
    }
    static close () {
        const popup_overlay = popup.overlay;
        const _popup = document.querySelector('popup.show');
        if ( ! _popup ){
            return;
        }
        _popup.classList.remove('show');
        if (document.querySelectorAll('popup.show, next-screen.show').length === 0){
            document.documentElement.style.overflow = 'auto';
        }
        if (document.querySelectorAll('popup.show').length === 0){
            popup_overlay.classList.remove('show');
        }
        _popup.dispatchEvent(new Event('onClose'));
    }
}
popup.init();