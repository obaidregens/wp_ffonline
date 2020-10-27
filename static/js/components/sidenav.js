const sidenav = class {
    static get overlay () {
        let sidenav_overlay = DOM.q('sidenav-overlay');
        if (! sidenav_overlay){
            sidenav_overlay = document.createElement("sidenav-overlay");
            document.documentElement.appendChild(sidenav_overlay);            
        }
        return sidenav_overlay;
    }
    static create(_sidenav,{onClose,onOpen} = {}) {
        if (onClose){
            _sidenav.addEventListener('onClose',onClose);
        }
        if (onOpen){
            _sidenav.addEventListener('onOpen',onOpen);
        }
        if (! _sidenav.parentNode){
            document.documentElement.appendChild(_sidenav);
            _sidenav.addEventListener('click',({target}) => {
                if (target.classList.contains('close_sidenav')) {
                    sidenav.close();
                }
            });
        }
    }
    static init () {
        const over = sidenav.overlay;
        over.addEventListener('click',sidenav.close);
    }
    static open (_sidenav) {
        popup.close();
        sidenav.close();
        _sidenav.classList.add('show');
        const sidenav_overlay = sidenav.overlay;
        sidenav_overlay.classList.add('show');
        DOM.q('body').classList.add('sidenav-collapse');
        document.documentElement.style.overflow = 'hidden';    
        _sidenav.dispatchEvent(new Event('onOpen'));
    }
    static close () {
        const _sidenav = DOM.q('sidenav.show');
        if ( ! _sidenav ){
            return;
        }
        const sidenav_overlay = sidenav.overlay;
        sidenav_overlay.classList.remove('show');

        _sidenav.dispatchEvent(new Event('onClose'));
        _sidenav.classList.remove('show');
        DOM.q('body').classList.remove('sidenav-collapse');
        if (DOM.qa('popup.show, next-screen.show, sidenav.show').length === 0){
            document.documentElement.style.overflow = 'auto';
        }
    }
}
sidenav.init();