const DOM = class {
    static q (selector) {
        return document.querySelector(selector);
    }
    static qa (selector) {
        return document.querySelectorAll(selector);
    }
    static create(tagName,opts = {}){
        return DOM.update(document.createElement(tagName),opts);
    }
    static update(the_elem,{
        classes = [],
        innerText = null,
        innerHTML = null,
        href = null,
        value = null,
        attributes = {},
        listeners = {},
        children = []
    }){
        if (classes.length > 0){
            the_elem.className = classes.join(' ');
        }
        if (innerText !== null){
            the_elem.innerText = innerText;
        }
        if (innerHTML !== null){
            the_elem.innerHTML = innerHTML;
        }
        if (href !== null){
            the_elem.href = href;
        }
        if (value !== null){
            the_elem.value = value;
        }
        const attr_entries = Object.entries(attributes);
        for (let i = 0; i < attr_entries.length; i++) {
            if (attr_entries[i][1] === null) {continue;}
            the_elem.setAttribute(attr_entries[i][0],attr_entries[i][1]);
        }
        const listener_entries = Object.entries(listeners);
        for (let i = 0; i < listener_entries.length; i++) {
            if (listener_entries[i][1] === null) {continue;}
            the_elem.addEventListener(listener_entries[i][0],listener_entries[i][1]);
        }
        the_elem = DOM.append(the_elem,children);
        return the_elem;
    }
    static append(to,elems){
        if (! elems instanceof Array){
            if (!elems){return;}
            to.appendChild(elems);
            return;
        }
        for (let i = 0; i < elems.length; i++) {
            if (!elems[i]){continue;}
            to.appendChild( elems[i] );
        }
        return to;
    }
}