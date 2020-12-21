class _ {
    static offsetTop(el) {
        const prev = el.style.position;
        el.style.position = 'static';
        const offset = el.offsetTop;
        el.style.position = prev;
        return offset;
    }
    static scrollBottom(el) {
        return el.offsetHeight+el.scrollTop;
    }
    static isSame(a,b) {
        return JSON.stringify(a) === JSON.stringify(b);
    }
    static elNode (text_or_el_node) {
        return text_or_el_node.nodeType === 1 ? text_or_el_node : text_or_el_node.parentElement;
    }
    static fitHeight (el) {
        const prevHeight = el.style.height;
        el.style.height = 'auto';
        const newHeight = el.scrollHeight;
        el.style.height = prevHeight;
        setTimeout(() => {
            window.requestAnimationFrame(() => {
                el.style.height = newHeight + 'px';
            });
        });
    }
    static childIndex (el) {
        let ii = 0;
        while (el.previousElementSibling) {
            ii++;
            el = el.previousElementSibling;
        }
        return ii;
    }
    static scrollTo (el) {
        const ht = DOM.q('html');
        el.scrollIntoView();
        ht.scrollTop = ht.scrollTop - 60;
    }
    static clone (obj) {
        return JSON.parse(JSON.stringify(obj));
    }
    static san (str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    }
    static debounce (func, wait = 600) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    static cmd(event) {
        return event.ctrlKey || event.metaKey;
    }
    static isHammerSwipe(event){
        const angle = event.type === "panright" ? Math.abs(event.angle) : 180 - Math.abs(event.angle);
        return angle < 20 && event.isFinal && event.distance > 20;
    }
    static prop (elem, prop, bool){
        if (bool) {
            elem.setAttribute(prop,'');
            return;
        }
        elem.removeAttribute(prop);
    }
    static moveAttr (from, to){
        const attributes = from.cloneNode(true).attributes;        
        for (let x = 0; x < attributes.length; x++) {
            const attr = attributes[x];
            to.setAttribute(attr.name,attr.value);
            from.removeAttribute(attr.name);
        }
        return true;
    }
    static htmlspecialchars_decode (str) {
        const tagsToReplace = {
            "&amp": "&",
            '&lt;': '<',
            '&gt;': '>',
            "&quot;": '\''
        };
        return str.replace(/&amp;/g,'&').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
    }
    static ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    static HexToRGB (hex) {
        const
            r = parseInt(hex.slice(1, 3), 16),
            g = parseInt(hex.slice(3, 5), 16),
            b = parseInt(hex.slice(5, 7), 16);
        return [r,g,b];
    }
    static camelToHyphen(key) {
        return key.replace( /([A-Z])/g, "-$1").toLowerCase();
    }
    static scrollEnd(func) {
        let scrollingTimer = 0;
        window.addEventListener('scroll', function ( event ) {
            clearTimeout( scrollingTimer );
            scrollingTimer = setTimeout(func, 100);
        }, {capture: false,passive: true});
    }
    static interact (func){
        if (! func){
            return false;
        }
        const events = ['touchstart','touchmove','click', 'wheel','mousemove','mousedown','mouseup','focus','blur','keydown','change','resize','scroll'];
        for (let i = 0; i < events.length; i++) {
            const event = events[i];
            window.addEventListener(event,func,{passive: true});
        }
        return true;
    }
    static array_unique(array){
        let to_return = [];
        for (let bb = 0; bb < array.length; bb++) {
            const element = array[bb];
            if (! to_return.includes(element)){
                to_return.push(element);
            }
        }
        return to_return;
    }
    static selectText(node) {    
        if (document.body.createTextRange) {
            const range = document.body.createTextRange();
            range.moveToElementText(node);
            range.select();
        } else if (window.getSelection) {
            const selection = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(node);
            selection.removeAllRanges();
            selection.addRange(range);
        } else {
            console.warn("Could not select text in node: Unsupported browser.");
        }
    }
    static copyText(text,do_toast = false){
        const copy_bubble = document.createElement('copy_bubble');
        copy_bubble.innerText = text;
        document.documentElement.appendChild(copy_bubble);
        _.selectText(copy_bubble);
        document.execCommand("copy");
        copy_bubble.remove();
        if (do_toast) {
            new toast("Copied");
        }
    }
}
window.expose = window.expose || {};
window.expose.copyText = _.copyText;
const _a = class {
    static intersect(array1,array2) {
        let loop_arr = array1;
        let in_arr = array2;
        if (array1.length > array2.length){
            loop_arr = array2;
            in_arr = array1;
        }
        const results_arr = [];
        for (let n = 0; n < loop_arr.length; n++) {
            if ( in_arr.includes(loop_arr[n]) ){
                results_arr.push(loop_arr[n]);
            }
        }
        return results_arr;
    }
    static diff(array,array1){
        return array.filter(function(element){
            if (! array1.includes(element)){
                return element;
            }
        });
    }
}
class _t {
    static isToday ( someDate ) {
        const today = new Date();
        return someDate.getDate() == today.getDate() &&
            someDate.getMonth() == today.getMonth() &&
            someDate.getFullYear() == today.getFullYear();
    }
    static stamp_duration(timestamp){
        var hours = Math.floor(timestamp / 60 / 60);
        var minutes = Math.floor(timestamp / 60) - (hours * 60);  
        var seconds = timestamp % 60;
      
        var formatted = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        return formatted;    
    }
    static local(time){
        let time_formatted = time.toString();
        time_formatted = time_formatted.split(' GMT')[0];
        time_formatted = time_formatted.substring(0,time_formatted.length-3);
        return time_formatted;    
    }
    static utcString(dt){
        const time = new Date(dt + ' UTC');
        return _t.local(time);    
    }
}