const next_screen = class {
    static create(_next,{onClose,onOpen} = {}) {
        if (onClose){
            _next.addEventListener('onClose',onClose);
        }
        if (onOpen){
            _next.addEventListener('onOpen',onOpen);
        }
        if (! _next.parentNode){
            document.documentElement.appendChild(_next);
            _next.addEventListener('click',({target}) => {
                if (target.classList.contains('close_next-screen')) {
                    next_screen.close();
                }
            });
            _next.appendChild(DOM.create('cross-button',{
                classes: ['close_next-screen']
            }));
        }
    }
    static init () {
        const all_next_screens = document.querySelectorAll('next-screen');
        for (let i = 0; i < all_next_screens.length; i++) {
            const trig = all_next_screens[i].previousElementSibling;
            if (trig.classList.contains('next-screen')){
                trig.addEventListener('click',next_screen.open.bind(null,all_next_screens[i]));
            }
            all_next_screens[i].addEventListener('click',({target}) => {
                if (target.classList.contains('close_next-screen')) {
                    next_screen.close();
                }
            });
            all_next_screens[i].appendChild(DOM.create('cross-button',{
                classes: ['close_next-screen']
            }));
        }    
    }
    static open (_next) {
        popup.close();
        next_screen.close();
        _next.classList.add('show');
        document.documentElement.style.overflow = 'hidden';    
        _next.dispatchEvent(new Event('onOpen'));
    }
    static close () {
        const _next = document.querySelector('next-screen.show');
        if ( ! _next ){
            return;
        }
        _next.dispatchEvent(new Event('onClose'));
        _next.classList.remove('show');
        if (document.querySelectorAll('popup.show, next-screen.show, sidenav.show').length === 0){
            document.documentElement.style.overflow = 'auto';
        }
    }
}
next_screen.init();