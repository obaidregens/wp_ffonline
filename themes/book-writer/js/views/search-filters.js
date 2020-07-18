function create_tags_popup(tag_name){
    const _tag_list = tag_list(tag_name);

    const _popup = document.createElement('popup');
    _popup.setAttribute('tag-name',tag_name);

    const wrap = document.createElement('wrap');

    const inc_button = document.createElement('button');
    inc_button.classList.add('include');
    inc_button.classList.add('active');
    
    const exc_button = document.createElement('button');
    exc_button.classList.add('exclude');
    
    inc_button.addEventListener('click',function(){
        inc_button.classList.add('active');
        exc_button.classList.remove('active');
        _tag_list.setAttribute('selection','include');

    });
    exc_button.addEventListener('click',function(){
        exc_button.classList.add('active');
        inc_button.classList.remove('active');
        _tag_list.setAttribute('selection','exclude');
    });

    const sort = document.createElement('button');
    sort.setAttribute('label','Sort');
    sort.classList.add('dropdown');
    const dropdown = document.createElement('dropdown');
    dropdown.classList.add('right');
    const count = document.createElement('li');
    count.innerText = 'Count';
    const alphabetical = document.createElement('li');
    alphabetical.innerText = 'Alphabetical';
    dropdown.appendChild(count);
    dropdown.appendChild(alphabetical);
    sort.appendChild(dropdown);

    const search = document.createElement('text-input');
    search.setAttribute('label','Search');
    search.addEventListener('input',function(){
        const _search = search.firstChild.value.toLowerCase();
        if (_search === ''){
            return;
        }
        const _boxes = _tag_list.querySelectorAll('[type=checkbox] + text');
        for (let i = 0; i < _boxes.length; i++) {
            const box = _boxes[i];
            if (box.innerText.toLowerCase().search(_search) !== -1){
                _popup.scrollTop = box.offsetTop - 150;
                break;
            }
        }
    });
    
    wrap.appendChild(inc_button);
    wrap.appendChild(exc_button);
    wrap.appendChild(sort);
    wrap.appendChild(search);

    const _wrap = document.createElement('wrap');
    const clear = document.createElement('button');
    clear.setAttribute('label','Clear');
    clear.addEventListener('click',function(){
        const _boxes = _tag_list.querySelectorAll('input:checked');
        for (let i = 0; i < _boxes.length; i++) {
            const box = _boxes[i];
            box.checked = false;
        }
    });

    const ok = document.createElement('button');
    ok.setAttribute('label','OK');
    ok.setAttribute('theme','');
    ok.classList.add('popup_close');
    
    _wrap.appendChild(clear);
    _wrap.appendChild(ok);

    _popup.appendChild(wrap);
    _popup.appendChild(_tag_list);

    _popup.appendChild(_wrap);
    return _popup;
}
function words(action = 'set'){
    let slider = document.querySelector('words-slider');
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
function tag_list(tag_name, sort = 'count'){
    const tags_data = JSON.parse(document.querySelector('tags_data').innerText);
    const this_tag_ids = Object.keys(tags_data[tag_name] || {});
    const sortFunc = sort === 'alphabetical' ? function(a, b){
        const nameA = tags_data[tag_name][a].name.toUpperCase();
        const nameB = tags_data[tag_name][b].name.toUpperCase();
        if (nameA < nameB) {
          return -1;
        }
        if (nameA > nameB) {
          return 1;
        }
        return 0;
    } : function(a, b){
        return tags_data[tag_name][a].count - tags_data[tag_name][b].count;
    };
    this_tag_ids.sort(sortFunc);
    if (sort === 'count'){
        this_tag_ids.reverse();
    }
    const tag_wrapper = document.createElement('tag_list');
    tag_wrapper.addEventListener('click',function(event){
        if (event.target.tagName.toLowerCase() === 'input'){
            if (tag_wrapper.getAttribute('selection') === 'exclude'){
                event.target.classList.add('cross');
            }
        }
    });
    for (let u = 0; u < this_tag_ids.length; u++) {
        const tag_id = this_tag_ids[u];
        const tag = tags_data[tag_name][tag_id];
        const tag_elem = document.createElement('checkbox');
        tag_elem.setAttribute('value',tag_id);
        tag_elem.setAttribute('label',tag.name + ' (' + tag.count + ')');
        tag_wrapper.appendChild(tag_elem);
    }
    return tag_wrapper;
}
const select_tags = document.querySelectorAll('select-tag');
for (let i = 0; i < select_tags.length; i++) {
    const elem = select_tags[i];
    const tag_name = elem.getAttribute('name');
    
    const tags_data = JSON.parse(document.querySelector('tags_data').innerText)[tag_name];
    const urlParams = new URLSearchParams(window.location.search);
    const url_selected = {
        included: urlParams.get(tag_name + '_included') ? urlParams.get(tag_name + '_included').split(',') : [],
        excluded: urlParams.get(tag_name + '_excluded') ? urlParams.get(tag_name + '_excluded').split(',') : []
    };
    elem.setAttribute('selected',JSON.stringify(url_selected));
    const url_mixed = url_selected.included.concat(url_selected.excluded);
    for (let yb = 0; yb < url_mixed.length; yb++) {
        const _tag = tags_data[url_mixed[yb]];
        const selected_tag_elem = document.createElement('tag');
        selected_tag_elem.innerText = _tag.name + ' (' + _tag.count + ')';
        if (url_selected.excluded.includes(url_mixed[yb])){
            selected_tag_elem.setAttribute('excluded','');
        }
        elem.appendChild(selected_tag_elem);
    }

    elem.addEventListener('click',function(){
        const _popup = create_tags_popup(tag_name);
        popup.create(_popup,{
            onClose: function() {
                const inputs = _popup.querySelectorAll('tag_list > label > input:checked');
                const selected = {included: [], excluded: []};
                const new_tags = document.createDocumentFragment();
                for (let i = 0; i < inputs.length; i++) {
                    const input = inputs[i];
                    const new_tag = document.createElement('tag');
                    new_tag.innerText = input.nextElementSibling.innerText;
                    if (input.classList.contains('cross')){
                        selected.excluded.push(input.value);
                        new_tag.setAttribute('excluded','');
                    }
                    else {
                        selected.included.push(input.value);
                    }
                    new_tags.appendChild(new_tag);
                }
                elem.innerText = '';
                elem.appendChild(new_tags);
                elem.setAttribute('selected',JSON.stringify(selected));
                _popup.remove();
            }
        });
        init_text_input();
        init_checkbox();
        
        const sort_drop = _popup.querySelector('button.dropdown > dropdown');
        const sort_btn = sort_drop.parentElement;
        sort_drop.addEventListener('click',function(event){
            sort_btn.blur();
            const list = tag_list(tag_name,event.target.innerText.toLowerCase());
            _popup.querySelector('tag_list').replaceWith(list);
            init_checkbox();
        });
        
        const selected_raw = elem.getAttribute('selected');
        const selected = selected_raw ? JSON.parse(selected_raw) : {included: [],excluded: []};
        const _mixed = selected.included.concat(selected.excluded);
        for (let m = 0; m < _mixed.length; m++) {
            const elem_ = _popup.querySelector(`input[type="checkbox"][value="${_mixed[m]}"]`);
            elem_.checked = true;
            if (selected.excluded.includes(_mixed[m])){
                elem_.classList.add('cross');
            }
        }
        popup.open(_popup);
    })
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
    construct += '&sort=' + document.querySelector('filter-books > next-screen > div > select').value;
    const search = document.querySelector('filter-books > next-screen > div > text-input:first-child > input').value;
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