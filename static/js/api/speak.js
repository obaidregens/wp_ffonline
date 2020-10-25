const speak = class {
    // Inits
    static inject_style(custom_style = "") {
        let style_string = "." + speak.classAdd + " {";
        if (custom_style !== "") {
            style_string += custom_style;
        }
        else {
            const mark_el = document.documentElement.appendChild(DOM.create('mark'));
            const styles_all = window.getComputedStyle(mark_el);
            const stylesClone = ['color','background-color'];
            stylesClone.forEach(style_single => {
                style_string += style_single + ":" + styles_all.getPropertyValue(style_single) + ";";
            });
            mark_el.remove();    
        }
        style_string += "}";
        document.querySelector('head').appendChild(DOM.create('style',{
            innerText: style_string
        }));
    }
    static hook_refresh() {
        // Some browsers don't cancel on refresh
        window.addEventListener('beforeunload',event => {
            if(window.speechSynthesis.speaking){
                window.onUnloadCancelSpeech = true;
                window.speechSynthesis.cancel();
            }
        });
    }
    // Promise based Wrapper for speech synthesis
    static speak (text,{onboundary,onend} = {}) {
        const utterance = new SpeechSynthesisUtterance(text);
        const oath =  new Promise((resolve) => {
            utterance.onend = (event) => {
                if (onend) {
                    onend(event);
                }
                resolve();
            };
            if (onboundary) {
                utterance.onboundary = onboundary;
            }
            speechSynthesis.speak(utterance);
        });
        return [oath,utterance];
    }
    // Handlers
    onboundary(event) {
        this.charIndex = event.charIndex || 0;
    }
    onend (event) {}
    // Methods
    constructor (content,resume = true) {
        if (!speak.initialized) {
            speak.hook_refresh();
            speak.inject_style("background-color: var(--grey);");
            speak.initialized = true;
        }
        this.content = content;
        this.paras = content.children;
        this.version = 0;
        this.current = 0;
        if (resume) {
            this.resume();
        }
    }
    pause () {
        this.speaking = false;
        this.version += 1;
        window.speechSynthesis.cancel();
        if (this.hasOwnProperty('current')) {
            this.paras[this.current].classList.remove(speak.classAdd);
        }
    }
    prev () {
        this.current = Math.max(0,this.current-1);
        this.resume();
    }
    next () {
        this.current = Math.min(this.paras.length-1,this.current+1);
        this.resume();
    }
    async resume (from = null) {
        this.pause();
        this.speaking = true;
        if (from === null) {
            from = this.current;
        }
        const current_version = this.version;
        for (let i = from; i < this.paras.length; i++) {
            if (this.version !== current_version || window.onUnloadCancelSpeech) {break;}
            this.current = i;
            const para = this.paras[i];
            _.scrollTo(para);
            para.classList.add(speak.classAdd);
            const [oath,utterance] = speak.speak(para.textContent);
            this.utterance = utterance;
            await oath;
            para.classList.remove(speak.classAdd);
            if (i === (this.paras.length-1) && this.onend){
                this.onend();
            }
        }
    }
}
speak.classAdd = "readAloud-highlight";