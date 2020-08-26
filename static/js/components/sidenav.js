const sidenav = class {
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
        const over = popup.overlay;
        over.addEventListener('click',sidenav.close);
    }
    static open (_sidenav) {
        popup.close();
        sidenav.close();
        _sidenav.classList.add('show');
        const popup_overlay = popup.overlay;
        popup_overlay.classList.add('show');
        document.querySelector('body').classList.add('sidenav-collapse');
        document.documentElement.style.overflow = 'hidden';    
        _sidenav.dispatchEvent(new Event('onOpen'));
    }
    static close () {
        const _sidenav = document.querySelector('sidenav.show');
        if ( ! _sidenav ){
            return;
        }
        const popup_overlay = popup.overlay;
        popup_overlay.classList.remove('show');

        _sidenav.dispatchEvent(new Event('onClose'));
        _sidenav.classList.remove('show');
        document.querySelector('body').classList.remove('sidenav-collapse');
        if (document.querySelectorAll('popup.show, next-screen.show, sidenav.show').length === 0){
            document.documentElement.style.overflow = 'auto';
        }
    }
}
sidenav.init();