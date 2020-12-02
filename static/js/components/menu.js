class menu {
    constructor (hook,children) {
        this.hook = hook;
        this.menu = document.documentElement.appendChild(DOM.create("options-menu",{
            children
        }));
        this.hook.addEventListener("contextmenu", event => {
            event.preventDefault();
            this.openedOn = this.getClosestElem(event.x,event.y);
            const {x,y} = this.getPosition(event);
            this.menu.style.top = y + 'px';
            this.menu.style.left = x + 'px';
            this.menu.classList.add('show');
            return false;
        });
        ['blur','mousedown','scroll','resize'].forEach(evt => window.addEventListener(evt,() => {
            this.menu.classList.remove('show');
        }));
    }
    getPosition(event) {
        const elHeight = 50;
        const elWidth = 160;
        const {x,y} = event;
        const xFrame = window.innerWidth;
        const yFrame = window.innerHeight;
        return {x: Math.min(x,xFrame-elWidth),y: Math.min(y,yFrame-elHeight)};
    }
    getCurrentSelection(para_el) {
        para_el = para_el || this.openedOn;
        const sel = window.getSelection();
        if (!sel || sel.isCollapsed === true) {
            return para_el.textContent;
        }
        const range = sel.getRangeAt(0);
        const commonAncestor = range.commonAncestorContainer;
        if (!this.hook.contains(commonAncestor)) {
            return para_el.textContent;
        }
        return sel.toString();
    }
    getClosestElem(x,y) {
        let el = document.elementFromPoint(x,y);
        while (el.tagName === "CONTENT") {
            x -= 10;
            y -= 10;
            el = document.elementFromPoint(x,y);
        }
        if (!el.closest('content')) {
            return false;
        }
        while (el.parentElement.tagName !== "CONTENT") {
            el = el.parentElement;
        }
        return el;
    }
}