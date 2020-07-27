function setSelectedTags(tag_name,selected){
    let tags_data = JSON.parse(document.querySelector('tags_data').innerText)[tag_name];
    if (tag_name === 'character'){
        const character_entries = Object.values(tags_data);
        tags_data = {};
        for (let i = 0; i < character_entries.length; i++) {
            tags_data = Object.assign(tags_data,character_entries[i]);
        }
    }
    const frag = document.createDocumentFragment();
    const select_tag = document.querySelector(`select-tag[name="${tag_name}"]`);
    select_tag.setAttribute('selected',JSON.stringify(selected));
    const url_mixed = selected.included.concat(selected.excluded);
    for (let yb = 0; yb < url_mixed.length; yb++) {
        const _tag = tags_data[url_mixed[yb]];
        const selected_tag_elem = document.createElement('tag');
        selected_tag_elem.innerText = _tag.name + ' (' + _tag.count + ')';
        if (selected.excluded.includes(url_mixed[yb])){
            selected_tag_elem.setAttribute('excluded','');
        }
        frag.appendChild(selected_tag_elem);
    }
    select_tag.innerText = '';
    select_tag.appendChild(frag);
}
const replaceTags = function(sort = 'count'){
    function sort_tags(tags,sort){
        const this_tag_ids = Object.keys(tags);
        const sortFunc = sort === 'alphabetical' ? function(a, b){
            const nameA = tags[a].name.toUpperCase();
            const nameB = tags[b].name.toUpperCase();
            if (nameA < nameB) {
              return -1;
            }
            if (nameA > nameB) {
              return 1;
            }
            return 0;
        } : function(a, b){
            return tags[a].count - tags[b].count;
        };
        this_tag_ids.sort(sortFunc);
        if (sort === 'count'){
            this_tag_ids.reverse();
        }
        return this_tag_ids;
    }
    function tags_checkboxes_fragment(tags,tag_ids){
        const fragment = document.createDocumentFragment();
        for (let u = 0; u < tag_ids.length; u++) {
            const tag_id = tag_ids[u];
            const tag = tags[tag_id];
            const tag_elem = DOM.create('label',{
                classes: ['checkbox'],
                children: [
                    DOM.create('input',{
                        attributes: {
                            type: 'checkbox',
                            value: tag_id,
                        }
                    }),
                    DOM.create('text',{
                        innerText: tag.name + ' (' + tag.count + ')'
                    })
                ]
            });
            fragment.appendChild(tag_elem);
        }
        return fragment;
    }
    const tags_data = JSON.parse(document.querySelector('tags_data').innerText);
    const current_popup = document.querySelector('popup[tag-name].show');
    if (! current_popup ){
        return;
    }
    const tag_name = current_popup.getAttribute('tag-name');
    let this_tags = tags_data[tag_name] || {};
    if (tag_name === 'character'){
        const fandom_ID = document.querySelector('popup[tag-name="character"] .glide__slide.glide__slide--active').getAttribute('value');
        this_tags = tags_data.character[fandom_ID] || {};
    }
    const this_tag_ids = sort_tags(this_tags, sort);
    const chkbx = tags_checkboxes_fragment(this_tags,this_tag_ids);
    // Selection
    const selected_raw = document.querySelector(`select-tag[name="${tag_name}"]`).getAttribute('selected');
    const selected = selected_raw ? JSON.parse(selected_raw) : {included: [],excluded: []};
    const _mixed = selected.included.concat(selected.excluded);
    for (let m = 0; m < _mixed.length; m++) {
        const elem_ = chkbx.querySelector(`input[type="checkbox"][value="${_mixed[m]}"]`);
        if (! elem_){
            continue;
        }
        elem_.checked = true;
        if (selected.excluded.includes(_mixed[m])){
            elem_.classList.add('cross');
        }
    }
    let insert_into = document.querySelector(`popup[tag-name="${tag_name}"] > tag_list`);
    if (tag_name === 'character'){
        insert_into = document.querySelector('popup[tag-name="character"] .glide__slide.glide__slide--active > tag_list');
    }
    insert_into.innerText = '';
    insert_into.appendChild(chkbx);

};
function saveSelectedTags(){
    const _pop_ = document.querySelector('popup[tag-name].show');
    if (! _pop_){
        return;
    }
    const tag_name = _pop_.getAttribute('tag-name');
    let tag_list = _pop_.querySelector('tag_list');
    let fandom_ID = null;
    const selected = {included: [], excluded: []};
    if (tag_name === 'character'){
        const tags_wrapper = _pop_.querySelector('.glide__track > ul > .glide__slide.glide__slide--active');
        fandom_ID = tags_wrapper.getAttribute('value');
        tag_list = tags_wrapper.querySelector('tag_list');
        const tags_data = JSON.parse(document.querySelector('tags_data').innerText);
        const characters = Object.keys(tags_data['character'][fandom_ID]);
        const selected_raw = document.querySelector(`select-tag[name="${tag_name}"]`).getAttribute('selected');
        const old_selected = selected_raw ? JSON.parse(selected_raw) : {included: [],excluded: []};
        selected.included = _a.diff(old_selected.included,characters);
        selected.excluded = _a.diff(old_selected.excluded,characters);
    }
    const inputs = tag_list.querySelectorAll(' label.checkbox > input:checked');
    for (let i = 0; i < inputs.length; i++) {
        const input = inputs[i];
        const loc = input.classList.contains('cross') ? 'excluded' : 'included';
        selected[loc].push(input.value);
    }
    setSelectedTags(tag_name,selected);
    tag_list.innerText = '';
}
function create_tags_popup(tag_name){
    let popup_content = DOM.create('tag_list');
    if (tag_name === 'character'){
        const slide_wrapper = DOM.create('ul',{
            classes: ['glide__slides']
        });
        const fandom_entries = Object.entries(JSON.parse(document.querySelector('tags_data').innerText)['fandom']);
        for (let i = 0; i < fandom_entries.length; i++) {
            const fandom_ID = fandom_entries[i][0];
            const fandom_name = fandom_entries[i][1].name;
            const slide = DOM.create('li',{
                classes: ['glide__slide'],
                attributes: {
                    label: fandom_name,
                    value: fandom_ID
                },
                children: [
                    DOM.create('tag_list')
                ]
            });
            slide_wrapper.appendChild(slide);
        }
        const glide = DOM.create('div',{
            classes: ['glide'],
            children: [
                DOM.create('div',{
                    attributes: {
                        "data-glide-el": "controls"
                    },
                    children: [
                        DOM.create('button',{
                            attributes: {
                                "data-glide-dir": "<"
                            }
                        }),
                        DOM.create('button',{
                            attributes: {
                                "data-glide-dir": ">"
                            }
                        })
                    ]
                }),
                DOM.create('div',{
                    classes: ['glide__track'],
                    attributes: {
                        "data-glide-el": "track"
                    },
                    children: [
                        slide_wrapper
                    ]
                })
            ]
        });
        popup_content = glide;
    }
    let _popup = DOM.create('popup',{
        attributes: {
            "tag-name": tag_name
        },
        listeners: {
            change: function(){
                if (
                    event.target.tagName.toLowerCase() === 'input' &&
                    event.target.getAttribute('type') === 'checkbox'
                ){
                    if (this.getAttribute('selection') === 'exclude'){
                        event.target.classList.add('cross');
                    }
                }
            },
            onClose: function(){
                saveSelectedTags();
                this.remove();
            }
        }
    });
    _popup = DOM.append(_popup,[
        DOM.create('wrap',{
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
                DOM.create('button',{
                    classes: ['dropdown'],
                    attributes: {
                        label: 'Sort'
                    },
                    children: [
                        DOM.create('dropdown',{
                            classes: ['right'],
                            children: [
                                DOM.create('li',{
                                    innerText: 'Count'
                                }),
                                DOM.create('li',{
                                    innerText: 'Alphabetical'
                                })
                            ],
                            listeners: {
                                click: function(){
                                    this.parentElement.blur();
                                    replaceTags(event.target.innerText.toLowerCase());
                                    init_checkbox();
                                }
                            }
                        }),
                    ]
                }),
                DOM.update(create_text_input({
                    label: 'Search'
                }),{
                    listeners: {
                        input: function(){
                            const _search = this.firstChild.value.toLowerCase();
                            if (_search === ''){
                                return;
                            }
                            const _popup = this.parentElement.parentElement;
                            const tag_name = _popup.getAttribute('tag-name');
                            let tag_list = _popup.querySelector('tag_list');
                            let offset = -120;
                            if (tag_name === 'character'){
                                offset = 35 ;
                                tag_list = _popup.querySelector(".glide__track > ul > .glide__slide.glide__slide--active > tag_list");
                            }
                            const _boxes = tag_list.querySelectorAll('label.checkbox > text');
                            for (let i = 0; i < _boxes.length; i++) {
                                const box = _boxes[i];
                                if (box.innerText.toLowerCase().search(_search) !== -1){
                                    _popup.scrollTop = box.offsetTop + offset;
                                    break;
                                }
                            }                        
                        }
                    }
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
    if (tag_name === 'character'){
        const glide_elem = _popup.querySelector('div.glide');
        const glide = new Glide(glide_elem,{
            type: 'carousel',
            perView: 1
        });
        glide.on('run.before',function(){
            saveSelectedTags();
        });
        function changeFandom(){
            const fandom_name = glide_elem.querySelector(`.glide__track > ul > .glide__slide--active`).getAttribute('label');
            glide_elem.querySelector('[data-glide-el="controls"]').setAttribute('fandom',fandom_name);
            replaceTags('count');
        }
        glide.on('run.after', changeFandom);
        glide.mount();
        changeFandom();
    }
    return _popup;
}
function words(action = 'set',words){
    const slider = document.querySelector('words-slider');
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
words();
const urlParams = new URLSearchParams(window.location.search);
const urlParamsWords = (urlParams.get('words') || '0,3000000').split(',');
document.querySelector('words-slider').noUiSlider.set([urlParamsWords[0], urlParamsWords[1]]);
const filters_search_elem = document.querySelector('filter-books > next-screen > div > text-input:first-child > input');
filters_search_elem.value = urlParams.get('search') || '';
filters_search_elem.dispatchEvent(new Event('change'));
const filters_sort_elem = document.querySelector('filter-books > next-screen > div > select');
const sort_options = [];
for (let k = 0; k < filters_sort_elem.children.length; k++) {
    sort_options.push(filters_sort_elem.children[k].getAttribute('value'));
}
filters_sort_elem.value = sort_options.includes(urlParams.get('sort')) ? urlParams.get('sort') : sort_options[0];
const select_tags = document.querySelectorAll('select-tag');
for (let i = 0; i < select_tags.length; i++) {
    const elem = select_tags[i];
    const tag_name = elem.getAttribute('name');
    
    const url_selected = {
        included: urlParams.get(tag_name + '_included') ? urlParams.get(tag_name + '_included').split(',') : [],
        excluded: urlParams.get(tag_name + '_excluded') ? urlParams.get(tag_name + '_excluded').split(',') : []
    };
    setSelectedTags(tag_name,url_selected);
    elem.addEventListener('click',function(){
        __pop = create_tags_popup(tag_name);
        popup.open(__pop);
        replaceTags();
    });
}
// Reset
document.querySelector('filter-books > next-screen > div > button[label="Reset"]').addEventListener('click',function(){
    words('reset');
    for (let i = 0; i < select_tags.length; i++) {
        const select_tag = select_tags[i];
        select_tag.removeAttribute('selected');
        select_tag.innerText = '';
    }
});
// Search
function trigger_search(page = false){
    document.querySelector('filter-books > next-screen > cross-button').dispatchEvent(new Event('click'));
    let search_progress_interval = 0;
    const loader = document.querySelector('loader');
    function progress_spinner(){
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

    let construct = 'words=' + words('get');
    construct += '&sort=' + filters_sort_elem.value;
    const search = filters_search_elem.value;
    construct += search === '' ? '' : '&search=' + search;
    const select_tags = document.querySelectorAll('select-tag');
    for (let i = 0; i < select_tags.length; i++) {
        const raw_selected = select_tags[i].getAttribute('selected');
        if (! raw_selected){
            continue;
        }
        const name = select_tags[i].getAttribute('name');
        const selected = JSON.parse(raw_selected);
        construct += selected.included.length === 0 ? '' : ('&' + name + '_included=' + selected.included.join(','));
        construct += selected.excluded.length === 0 ? '' : ('&' + name + '_excluded=' + selected.excluded.join(','));
    }
    const prev_ss = document.querySelector('prev_ss');
    api('search',{
        data: {
            search: construct,
            page,
            placeholder: document.querySelector('placeholder_data').innerText,
            prev: prev_ss.innerText
        },
        dataType: 'JSON',
        callback: function(response){  
            // New Data
            prev_ss.innerText = response.prev;
            document.querySelector('tags_data').innerText = JSON.stringify( response.tags_data );
            document.querySelector('pagination').innerHTML = response.paginate;
            document.querySelector('books-container').innerHTML = response.output;
            window.history.pushState("object or string", document.querySelector("title").innerText,'?' + construct);
            
            // Styling
            clearInterval(search_progress_interval);
            loader.classList.remove('show');
        }
    });

}
document.querySelector('filter-books > next-screen > div > button[label="Search"]').addEventListener('click',function(event){
    event.preventDefault();
    trigger_search();
});
document.querySelector('pagination').addEventListener('click',function(event){
    event.preventDefault();
    const to = parseInt(event.target.getAttribute('paginate'));
    if (to){
        trigger_search(to);
    }
});