function init_next_screen(){
    const all_next_screens = document.querySelectorAll('next-screen');
    for (let i = 0; i < all_next_screens.length; i++) {
        const elem = all_next_screens[i];
        const cross_button = document.createElement('cross-button');
        cross_button.addEventListener('click',function(){
            elem.classList.remove('show');
            if (document.querySelectorAll('next-screen.show, popup.show').length === 0){
                document.documentElement.style.overflow = 'auto';
            }
        });
        elem.appendChild(cross_button);
    }
    document.documentElement.addEventListener('click', function(event) {
        if (event.target.classList.contains('close_next-screen')){
            document.querySelector('next-screen.show').classList.remove('show');
            if (document.querySelectorAll('next-screen.show, popup.show').length === 0){
                document.documentElement.style.overflow = 'auto';
            }
            return;
        }
        if (! event.target.classList.contains('next-screen')) {
            return;
        }
        const next_screen = event.target.nextElementSibling;
        if (next_screen.tagName.toLowerCase() !== 'next-screen'){
            return;
        }
        next_screen.classList.add('show');
        document.documentElement.style.overflow = 'hidden';
    });    
}
init_next_screen();