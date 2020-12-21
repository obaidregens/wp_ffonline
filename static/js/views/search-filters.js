const tagNamesCacheGet = JSON.parse(localStorage.getItem('tag_names')) || {};
const tagNamesCache = ((Date.now() - parseInt(tagNamesCacheGet.cache_time || 0)) > 30*60*1000) ? {} : tagNamesCacheGet;
const setSelectedTags = async (tag_name,selected) => {
    const frag = document.createDocumentFragment();
    const select_tag = DOM.q(`select-tag[name="${tag_name}"]`);
    select_tag.setAttribute('selected',JSON.stringify(selected));
    const url_mixed = selected.included.concat(selected.excluded);
    tagNamesCache.cache_time = tagNamesCache.cache_time || Date.now();
    tagNamesCache[tag_name] = tagNamesCache[tag_name] || {};
    const getIds = [];
    url_mixed.forEach((id,i) => {
        if (!tagNamesCache[tag_name][id]) {
            getIds.push(id);
        }
    });
    if (getIds.length > 0) {
        const {names} = await api("get_tags",{
            data: {
                tag: tag_name,
                ids: getIds
            }
        });
        Object.assign(tagNamesCache[tag_name],names);
    }
    url_mixed.forEach(tag_id => {
        const _tag = tagNamesCache[tag_name][tag_id];
        if (! _tag) {
            return;
        }
        frag.appendChild(DOM.create('tag',{
            innerText: `${_tag}`,
            attributes: {
                excluded: selected.excluded.includes(tag_id) ? "" : null
            }
        }));
    });
    select_tag.innerText = '';
    select_tag.appendChild(frag);
    localStorage.setItem('tag_names',JSON.stringify(tagNamesCache));
}
const setFilterFandom = (fandomN) => {
    const sort_elem = DOM.q('filter-books > next-screen > div > select');
    const fandom_showing = DOM.q('fandom-filter > showing');
    fandom_showing.classList.add('show');
    fandom_showing.querySelector('select').value = sort_elem.value;
    fandom_showing.querySelector('fandom').innerText = fandomN === "" ? "All" : fandomN;
}
(() => {
    const saveSelectedTags = () => {
        const _pop_ = DOM.q('popup[tag-name].show');
        if (! _pop_){
            return;
        }
        const tag_name = _pop_.getAttribute('tag-name');
        const tag_list = _pop_.querySelector('tag_list');
        const selected = {included: [], excluded: []};
        const inputs = tag_list.querySelectorAll(' label.checkbox > input:checked');
        for (let i = 0; i < inputs.length; i++) {
            const input = inputs[i];
            const loc = input.classList.contains('cross') ? 'excluded' : 'included';
            selected[loc].push(input.value);
        }
        setSelectedTags(tag_name,selected);
    }
    const checkboxFrag = function (list,opts) {
        const fragment = document.createDocumentFragment();
        list.forEach(({value,name,count,selected = null}) => {
            const checkboxEl = DOM.create('label',{
                classes: ['checkbox'],
                children: [
                    DOM.create('input',{
                        attributes: {
                            type: 'checkbox',
                            value: value,
                            checked: (selected === true || selected === false) ? "" : null
                        },
                        classes: selected === false ? ['cross'] : []
                    }),
                    DOM.create('text',{
                        innerText: `${name} (${count})`
                    })
                ]
            });
            fragment.appendChild(checkboxEl);
        });
        if (opts.more) {
            fragment.appendChild(DOM.create("text",{
                innerText: `Search to see more.`
            }));
        }
        this.drop.innerText = "";
        this.drop.appendChild(fragment);
        return fragment;
    }
    const create_tags_popup = (tag_name) => {
        let popup_content = DOM.create('tag_list');
        let _popup = DOM.create('popup',{
            classes: ['async'],
            attributes: {
                "tag-name": tag_name
            },
            listeners: {
                change: function(event){
                    if (event.target.getAttribute('type') === 'checkbox'){
                        if (this.getAttribute('selection') === 'exclude'){
                            event.target.classList.add('cross');
                        }
                        saveSelectedTags();
                    }
                },
                onClose: saveSelectedTags,
                onAfterClose: function() {
                    setTimeout(() => this.remove(),100);
                }
            }
        });
        _popup = DOM.append(_popup,[
            [].includes(tag_name) ? null : DOM.create('wrap',{
                children: [
                    DOM.create('button',{
                        classes: ['include','active'],
                        listeners: {
                            "click": function(){
                                this.classList.add('active');
                                this.nextElementSibling.classList.remove('active');
                                _popup.setAttribute('selection','include');                        
                            }
                        }
                    }),
                    DOM.create('button',{
                        classes: ['exclude'],
                        listeners: {
                            "click": function(){
                                this.classList.add('active');
                                this.previousElementSibling.classList.remove('active');
                                _popup.setAttribute('selection','exclude');                        
                            }
                        }
                    }),
                    create_text_input({
                        label: 'Search'
                    })
                ]
            }),
            popup_content,
            DOM.create('wrap',{
                children: [
                    DOM.create('button',{
                        attributes: {
                            label: 'Clear'
                        },
                        listeners: {
                            click: function(){
                                const _boxes = _popup.querySelectorAll('input:checked');
                                for (let i = 0; i < _boxes.length; i++) {
                                    const box = _boxes[i];
                                    box.checked = false;
                                }                        
                            }
                        }
                    }),
                    DOM.create('button',{
                        classes: ['popup_close'],
                        attributes: {
                            theme: '',
                            label: 'OK'
                        }
                    })
                ]
            })
        ]);
        popup.create(_popup);
        return _popup;
    }
    const words = (action = 'set',words) => {
        const slider = DOM.q('words-slider');
        if (slider.noUiSlider){
            if (action === 'reset'){
                slider.noUiSlider.reset();
            }
            else if (action === 'get'){
                let words = slider.noUiSlider.get();
                words[0] = parseInt(words[0].replace(/\,/g,'') );
                words[1] = parseInt(words[1].replace(/\,/g,'') );
                words = words.join();
                return words;
            }
            return;
        }
        const range = {
            'min': [0,500],//0,500
            '10%': [1000,1000],//1K
            '15%': [2000,3000],//2K
            '20%': [5000,5000],//5K,10K,15K
            '35%': [20000,10000],//20K,30K,40K
            '50%': [50000,25000],//50K,75K
            '60%': [100000,50000],//100K,150K
            '70%': [200000,100000],//200K
            '75%': [300000,200000],//300K
            '80%': [500000,500000],//500K
            '85%': [1000000,1000000],//1M,2M
            'max': [3000000] //3M
        };
        noUiSlider.create(slider, {
            start: [0, 3000000],
            connect: true,
            orientation: 'horizontal',
            range,
            tooltips: [true,true],
            format: wNumb({
                decimals: 0,
                thousand: ',',
                suffix: ' Words',
            }),
        });
    }
    const urlParams = new URLSearchParams(window.location.search);

    // Words
    words();
    const urlParamsWords = (urlParams.get('words') || '0,3000000').split(',');
    DOM.q('words-slider').noUiSlider.set([urlParamsWords[0], urlParamsWords[1]]);

    // Search
    const search_el = DOM.q('filter-books > next-screen > div > text-input:first-child > input');
    search_el.value = urlParams.get('search') || '';
    search_el.dispatchEvent(new Event('change'));

    // Author Search
    const author_el = DOM.q('filter-books > next-screen > div > text-input:nth-child(2) > input');
    author_el.value = urlParams.get('author') || '';
    author_el.dispatchEvent(new Event('change'));

    // Sort
    const sort_el = DOM.q('filter-books > next-screen > div > select');
    const sort_options = [...sort_el.children].map(sl => sl.getAttribute('value'));
    sort_el.value = sort_options.includes(urlParams.get('sort')) ? urlParams.get('sort') : sort_options[0];

    const select_tags = DOM.qa('select-tag');
    select_tags.forEach(elem => {
        const tag_name = elem.getAttribute('name');
        
        const url_selected = {
            included: urlParams.get(tag_name + '_included') ? urlParams.get(tag_name + '_included').split(',') : [],
            excluded: urlParams.get(tag_name + '_excluded') ? urlParams.get(tag_name + '_excluded').split(',') : []
        };
        setSelectedTags(tag_name,url_selected);
        elem.addEventListener('click',function(){
            __pop = create_tags_popup(tag_name);
            popup.open(__pop);
            setTimeout(() => {
                const SearchInput = __pop.querySelector('wrap text-input > input');
                const ac = new autocomplete(SearchInput,[],{
                    name: "filter-" + tag_name,
                    async: "load_tags",
                    async_data: {
                        tag: tag_name,
                        prev: DOM.q('prev_ss').innerText,
                        selected: () => JSON.parse(elem.getAttribute('selected'))
                    },
                    select: false,
                    outOnMove: false,
                    renderList: function () {
                        this.drop = __pop.querySelector('tag_list');
                    }
                });
                ac.render = checkboxFrag
                SearchInput.dispatchEvent(new Event('focus'));
            });
        });
    });
    // Reset
    DOM.q('filter-books > next-screen > div > button[label="Reset"]').addEventListener('click',function(){
        words('reset');
        for (let i = 0; i < select_tags.length; i++) {
            const select_tag = select_tags[i];
            select_tag.removeAttribute('selected');
            select_tag.innerText = '';
        }
    });
    // Search
    const trigger_search = (page = false) => {
        next_screen.close();
        let search_progress_interval = 0;
        const loader = DOM.q('loader');
        const progress_spinner = () => {
            loader.classList.add('show');
            loader.setAttribute('progress', '0%');
            search_progress_interval = setInterval(function(){
                const prev = parseInt(loader.getAttribute('progress'));
                if (prev >= 100){
                    clearInterval(search_progress_interval);
                    return;
                }
                loader.setAttribute('progress',(prev + 1) + '%');
            },100);
        }
        progress_spinner();
    
        const construct = [
            "words=" + words('get'),
            'sort=' + sort_el.value
        ];
        const search = search_el.value;
        if (search !== "") {
            construct.push('search=' + search);
        }
        const author_search = author_el.value;
        if (author_search !== ""){
            construct.push('author=' + author_search);
        }
        const select_tags = DOM.qa('select-tag');

        for (let i = 0; i < select_tags.length; i++) {
            const raw_selected = select_tags[i].getAttribute('selected');
            if (! raw_selected){
                continue;
            }
            const _selected = JSON.parse(raw_selected);
            const name = select_tags[i].getAttribute('name');
            if (_selected.included.length > 0) {
                construct.push(name + '_included=' + _selected.included.join(','));
            }
            if (_selected.excluded.length > 0) {
                construct.push(name + '_excluded=' + _selected.excluded.join(','));
            }
        }
        const prev_ss = DOM.q('prev_ss');
        api('search',{
            data: {
                search: construct.join('&'),
                page,
                prev: prev_ss.innerText
            }
        })
        .then(response => {
            // Fandom Filter
            if (construct.length > 2) {
                const fandom_name = [...DOM.q('select-tag[name="fandom"]').children].map(tag_el => tag_el.innerText).join("/");
                setFilterFandom(fandom_name);
            }
            else {
                const fandom_showing = DOM.q('fandom-filter > showing');
                fandom_showing.classList.remove('show');
                DOM.q('fandom-filter > input').value = "";
            }

            // New Data
            prev_ss.innerText = response.prev;
            collections.book_collections = response.book_collections;
            DOM.q('pagination').innerHTML = response.paginate;
            DOM.q('books-container').innerHTML = response.output;
            window.history.pushState("object or string", DOM.q("title").innerText,'?' + construct.join("&"));
            
            // Styling
            clearInterval(search_progress_interval);
            loader.classList.remove('show');
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    
            reChapterProgress();
            reHookOffline();
        });
    
    }
    DOM.q('fandom-filter select').addEventListener('change',({target}) => {
        sort_el.value = target.value;
        DOM.q('[label="Search"]').dispatchEvent(new Event("click"));
    });
    DOM.q('filter-books > next-screen > div > button[label="Search"]').addEventListener('click',function(event){
        event.preventDefault();
        trigger_search();
    });
    DOM.q('pagination').addEventListener('click',function(event){
        event.preventDefault();
        const to = parseInt(event.target.getAttribute('paginate'));
        if (to){
            trigger_search(to);
        }
    });
})();