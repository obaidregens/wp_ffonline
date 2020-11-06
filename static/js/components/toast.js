const toastContain = DOM.create('toasts');
document.documentElement.appendChild(toastContain);
const transitionLength = parseFloat(getComputedStyle(toastContain).getPropertyValue('--toast-transition')) * 1000;
class toast {
    constructor(str, time = 2000, classes = []) {
        const toastEl = DOM.create('toast',{
            innerText: str,
            classes
        });
        toastContain.prepend(toastEl);
        setTimeout(() => toastEl.setAttribute('open',''));
        setTimeout(
            () => toastEl.removeAttribute('open'),
            time
        );
        setTimeout(
            () => toastContain.removeChild(toastEl),
            time + transitionLength
        );
    }
}