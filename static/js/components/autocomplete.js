class autocomplete {
    constructor (input, list, opts) {
        this.opts = Object.assign({
            name: "",
            lowercase: true,
            trim: true,
            select: true,
            limit: false,
            preview: true,
            none_found_msg: "No options",
            maxHeight: 200,
            async: false,
            async_data: {},
            renderList: null,
            outOnMove: true,
            debounceTime: 200
        },opts);
        this.input = input;
        this.list = list;
        const enterInput = async ({target}) => {
            const se = await this.search(target.value);
            this.render(se.list,{empty_search: se.empty,more: se.more});
            this.change(se);
        };
        if (this.opts.outOnMove) {
            input.addEventListener('blur',this.out.bind(this));
            window.addEventListener('resize',this.out.bind(this));
            window.addEventListener('scroll',this.out.bind(this));    
        }
        input.addEventListener('focus',_.debounce(enterInput,this.opts.debounceTime));
        input.addEventListener('input',_.debounce(enterInput,this.opts.debounceTime));
        if (this.opts.select) {
            input.addEventListener('keydown',(event) => {
                if (!["ArrowDown","ArrowUp"].includes(event.key)) {
                    return;
                }
                event.preventDefault();
                this.focused_list = true;
                this.drop[event.key === "ArrowDown" ? "firstChild" : "lastChild"].focus();
            });
        }
        if (this.opts.renderList !== null) {
            this.renderList = this.opts.renderList.bind(this);
        }
        this.renderList();
    }
    renderList (list) {
        this.drop = DOM.create('ul',{
            attributes: {
                "autocomplete": ""
            },
            classes: this.opts.select ? ['select']  : [],
            listeners: !this.opts.select ? {} : {
                keydown: event => {
                    if (!["ArrowUp","ArrowDown","Enter"].includes(event.key)) {
                        return;
                    }
                    event.preventDefault();
                    if (event.key === "Enter") {
                        return this.select({
                            name: event.target.innerText,
                            value: event.target.getAttribute('value')
                        });
                    }
                    this.focused_list = true;
                    if (event.key === "ArrowDown") {
                        const next = event.target.nextElementSibling;
                        next ? next.focus() : event.target.parentElement.firstChild.focus();
                    }
                    else if (event.key === "ArrowUp") {
                        const prev = event.target.previousElementSibling;
                        prev ? prev.focus() : event.target.parentElement.lastChild.focus();
                    }
                },
                mousedown: event => {
                    event.preventDefault();
                    if (event.target.tagName.toLowerCase() !== 'li') {
                        return;
                    }
                    return this.select(event.target.refValue);
                }
            },
            children: [],
        });
        document.documentElement.appendChild(this.drop);
    }
    render (list,opts) {
        opts = Object.assign({
            empty_search: false
        },opts);
        this.drop.innerText = "";
        DOM.append(this.drop,(list.length === 0 && this.opts.none_found_msg) ? [DOM.create('text',{
            innerHTML: this.opts.none_found_msg
        })] : ((!this.opts.preview && opts.empty_search) ? [] : list.map((Obj,i) => {
            const thisDOM =  DOM.create('li',{
                innerText: Obj.name,
                attributes: {
                    value: Obj.value,
                    tabindex: this.opts.select ? i : null
                },
                listeners: {
                    blur: this.out.bind(this)
                }
            });
            thisDOM.refValue = Obj;
            return thisDOM;
        })));
        const rect = this.input.getBoundingClientRect();
        this.drop.style.top = rect.bottom + "px";
        this.drop.style.width = rect.width + "px";
        this.drop.style.left = rect.left + "px";
        this.setHeight();
    }
    setHeight() {
        const prevHeight = this.drop.style.height;
        this.drop.style.height = 'auto';
        const newHeight = this.drop.scrollHeight;
        this.drop.style.height = prevHeight;
        setTimeout(() => {
            this.drop.style.height = Math.min(newHeight,this.opts.maxHeight) + 'px';
        });
    }
    out () {
        if (this.focused_list) {
            this.focused_list = false;
            return;
        }
        if (this.drop && this.drop.parentElement) {
            this.drop.style.height = "0px";
        }
    }
    async search(search) {
        this.preChange();
        let s = search;
        
        let res = {};
        if (this.opts.async) {
            const data = {search};
            Object.entries(this.opts.async_data).forEach(([key,value]) => {
                if(typeof value === 'function') {
                    data[key] = value();
                    return;
                }
                data[key] = value;
            });
            const uniqKey = JSON.stringify(data);
            const cached = this.getCache(uniqKey);
            if (cached !== false) {
                return cached;
            }
    
            const {result = {}} = await api(this.opts.async,{
                data
            });
            res = {
                search,
                exactMatch: result.exactMatch || false,
                list: result.list || [],
                more: result.more || false,
                empty: (this.opts.trim ? search.trim() : search) === ""
            };
            this.putCache(uniqKey,res);
            return res;
        }
        const cached = this.getCache(search);
        if (cached !== false) {
            return cached;
        }

        if (this.opts.lowercase) {
            s = s.toLowerCase();
        }
        if (this.opts.trim) {
            s = s.trim();
        }
        let exactMatch  = false;
        const newList = [];
        for (let i = 0; i < this.list.length; i++) {
            let t = this.list[i].name;
            if (this.opts.lowercase) {
                t = t.toLowerCase();
            }
            if (this.opts.trim) {
                t = t.trim();
            }
            if (t.search(s) !== -1) {
                newList.push(this.list[i]);
            }
            if (t === s) {
                exactMatch = true;
            }
            if (this.opts.limit !== false && newList.length >= this.opts.limit) {
                break;
            }
        }
        res = {
            search,
            exactMatch,
            list: newList,
            empty: s === "",
            more: false
        };
        this.putCache(search,res);
        return res;
    }
    cacheKey(search) {
        return `ac+${this.opts.name}-${search}`;
    }
    getCache(search) {
        if (!this.opts.name) {
            return false;
        }
        const cached = JSON.parse(localStorage.getItem(this.cacheKey(search)));
        if (cached === null || ( (Date.now() - parseInt(cached.cache_time || 0))) > 30*60*1000 ) {
            return false;
        }
        return cached;
    }
    putCache(search,res) {
        if (!this.opts.name) {
            return false;
        }
        res.cache_time = Date.now();
        localStorage.setItem(this.cacheKey(search),JSON.stringify(res));
    }
    change({exactMatch,list}) {}
    preChange(search) {}
    select (value) {}
}