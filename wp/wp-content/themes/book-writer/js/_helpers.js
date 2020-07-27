class _ {
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
    static ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    static camelToHyphen(key) {
        return key.replace( /([A-Z])/g, "-$1").toLowerCase();
    }
    static interact (func){
        if (! func){
            return false;
        }
        const events = ['touchstart', 'touchmove', 'click', 'wheel', 'mousedown', 'mouseup', 'focus', 'blur', 'keydown', 'change', 'resize', 'scroll'];
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
    static copyText(text){
        const copy_bubble = document.createElement('copy_bubble');
        copy_bubble.innerText = text;
        document.documentElement.appendChild(copy_bubble);
        _.selectText(copy_bubble);
        document.execCommand("copy");
        copy_bubble.remove();
    }
}
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