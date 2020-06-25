const all_tax = ['rating','language','status','fandom','genre','character','pairing','tag'];
function search_settings(method){
    if (method == 'open'){
        jQuery('#search-settings').css('display','block');
        jQuery('#search-display').css('display','none');
    }
    else if (method == 'close'){
        jQuery('#search-settings').css('display','none');
        jQuery('#search-display').css('display','block');
    }
}

jQuery(".search-tags").keyup(function(event){
    var search = this.value.toLowerCase();
    var invalidKeys = [13,38,40,39,37,33,34,16,17,255,18,20,9];
    if (invalidKeys.indexOf(event.keyCode) != -1 || search == ''){
        return;
    }
    var modal_id = jQuery(this).parents('.custom-modal')[0].id;
    var boxes = jQuery('#' + modal_id + ' [type=checkbox] + text');
    for (var g = 0; g < boxes.length; g++) {
        if (boxes[g].innerHTML.toLowerCase().search(search) != -1){
            jQuery('#' + modal_id).animate({
                scrollTop: boxes[g].offsetTop-112
            }, 0);
            break;
        }
    }
    
});
function trigger_include(group){
    jQuery('#' + group + '-select').attr('select_status','');
    jQuery('#' + group + '-select .btn-include').addClass('active');
    jQuery('#' + group + '-select .btn-exclude').removeClass('active');
    
}
function trigger_exclude(group){
    jQuery('#' + group + '-select').attr('select_status','cross');
    jQuery('#' + group + '-select .btn-include').removeClass('active');
    jQuery('#' + group + '-select .btn-exclude').addClass('active');
}
function clear_checkboxes(group){
    jQuery('#' + group + '-select [type=checkbox]:checked').prop("checked", false);
}
function get_selected(group,output){
    var all = jQuery('#' + group + '-select [type=checkbox]:checked');
    var exclude = [];
    var include = [];
    if (output == 'value'){
        for (var g = 0; g < all.length; g++) {
            if (jQuery(all[g]).hasClass('cross') == true){
                exclude.push(all[g].value);
            }
            else{
                include.push(all[g].value);
            }            
        }
    }           
    else if (output == 'name'){
        for (var g = 0; g < all.length; g++) {
            if (jQuery(all[g]).hasClass('cross') == true){
                exclude.push(jQuery(all[g]).siblings('text')[0].innerHTML);
            }
            else{
                include.push(jQuery(all[g]).siblings('text')[0].innerHTML);
            }            
        }
    }
    return {
        included: include,
        excluded: exclude
    };
    
}
function set_selected(group,{included, excluded}){
    const _mixed = included.concat(excluded);
    for (let m = 0; m < _mixed.length; m++) {
        const elem_ = jQuery(`#${group}-select [type="checkbox"][value="${_mixed[m]}"]`)[0];
        jQuery(elem_).prop('checked',true);
        if (excluded.includes(_mixed[m])){
            jQuery(elem_).addClass('cross');
        }
    }
    trigger_custom_modal(group + '-select');
    trigger_custom_modal(group + '-select');
}
function reset_settings(){
    var tax = all_tax;
    for (var i = 0; i < tax.length; i++) {
        clear_checkboxes(tax[i]);
        jQuery('#' + tax[i] + '_show .chips_s').children().remove();
        
        const all_label = jQuery('#' + tax[i] + '_show .all_label')[0];
        all_label.innerHTML = '<div class="grey-text">All</div>';
    }
    jQuery('#sort').val('modified/DESC');
    jQuery('#search').val('');
    const slider = document.getElementById('words-slider');
    words_set();
    slider.noUiSlider.set([0,3000000]);
}
function trigger_custom_modal(id){
    const elem = document.getElementById(id);
    const dsply = elem.style.display;
    if (dsply === 'none' || dsply === ''){
        elem.style.display = 'block';
        document.getElementById('custom_overlay').style.display = 'block'
    }
    else if (dsply === 'block'){
        close_custom_modal();
    }
}
function close_custom_modal(){
    const elems = jQuery('[id$="-select"]');
    let this_elem = '';
    for (let i = 0; i < elems.length; i++) {
        if (jQuery(elems[i]).css('display') == 'block'){
            this_elem = elems[i];
            break;
        }
    }
    const group = this_elem.id.replace('-select','');
    const selected = get_selected(group,'name');
    jQuery('#' + group + '_show .chips_s').children().remove();
    jQuery('#' + group + '_show .all_label').children().remove();
    if (selected.included.length == 0 && selected.excluded.length == 0){
        jQuery('#' + group + '_show .all_label').append('<div class="grey-text">All</div>');
    }
    let included_construct = '';
    for (let i = 0; i < selected.included.length; i++) {
        included_construct += '<div class="chip">' + selected.included[i] + '</div>';
    }
    jQuery('#' + group + '_show .included_chips')[0].innerHTML = included_construct;
    let excluded_construct = '';
    for (let i = 0; i < selected.excluded.length; i++) {
        excluded_construct += '<div class="chip">' + selected.excluded[i] + '</div>';
    }
    jQuery('#' + group + '_show .excluded_chips')[0].innerHTML = excluded_construct;
    jQuery(this_elem).css('display','none');
    jQuery('#custom_overlay').css('display','none');
    
}

function trigger_search(page = false){
    let search_progress_interval = 0;
    function progress_spinner(){
        spin('#box','center');
        jQuery('#box').append(`<div id="progress-within-spinner"
        style="
            position: absolute;
            text-align: center;
            top: 26%;
            width: 100%;
            left: 0;
            font-size: 16px;
            font-weight: bold;
            color: var(--theme-color);
            user-select: none;
        ">0%</div>`);
        search_progress_interval = setInterval(function(){
            const this_elem = document.getElementById('progress-within-spinner');
            const prev = parseInt(this_elem.innerHTML);
            if (prev >= 100){
                clearInterval(search_progress_interval);
                return;
            }
            this_elem.innerHTML = (prev+1) + '%';
        },100);
        document.getElementById('pagination-wrapper').innerHTML = '';   
        jQuery("#search-btn").addClass("disabled");
        search_settings('close');    
    }
    progress_spinner();
    const slider = document.getElementById('words-slider');
    let words = slider.noUiSlider.get();
    words[0] = parseInt(words[0].replace(/[,]+/g,'').replace(' Words',''));
    words[1] = parseInt(words[1].replace(/[,]+/g,'').replace(' Words',''));
    words = words.join();
    const data = {
        words,
        sort: jQuery('#sort').val(),
    };
    const val = jQuery('#search').val();
    if (val !== ''){
        data['search'] = val;
    }
    const multi_tax = all_tax;
    for (let i = 0; i < multi_tax.length; i++) {
        const selected = get_selected(multi_tax[i],'value');
        if (selected.included.length != 0){
            data[multi_tax[i] + '_included'] = selected.included.join();
        }
        if (selected.excluded.length != 0){
            data[multi_tax[i] + '_excluded'] = selected.excluded.join();
        }
    }
    let construct = '';
    const keys = Object.keys(data);
    for (let i = 0; i < keys.length; i++) {
        if (keys[i] == 'ajax' || keys[i] == 'search_id'){
            continue;
        }
        construct += keys[i] + '=' + data[keys[i]];
        if (i < keys.length-1){
            construct += '&';
        }
    }
    api('search',{
        data: {
            search: construct,
            page,
            placeholder: document.getElementById('placeholder_data').innerHTML,
            prev: document.getElementById('prev_ss').innerHTML
        },
        dataType: 'JSON',
        callback: function(response){
            // Styling
            clearInterval(search_progress_interval);
            jQuery("#box").css('text-align','left');
            jQuery('#progress-within-spinner').remove();
            jQuery("#search-btn").removeClass("disabled");
            
            // New Data
            const response_arr  = response;
            document.getElementById('prev_ss').innerHTML = response_arr['prev'];
            document.getElementById('tags_data').innerHTML = JSON.stringify( response_arr['tags_data'] );
            document.getElementById('pagination-wrapper').innerHTML = response_arr.paginate;
            document.getElementById('box').innerHTML = response_arr['output'];
            window.history.pushState("object or string", document.getElementsByTagName("title")[0].innerHTML,'?' + construct);

            // Reinit
            jQuery('.dropdown-trigger:not(.sort-tags-drop-button)').dropdown();
            jQuery('.modal:not(#searchbook)').modal();
            book_collections_init();
            load_tags_data();
            filters_from_url();
        }
    });

}
jQuery("form[name='search']").submit(function(event) {
    event.preventDefault();
    trigger_search();
});
function paginate(to){
    trigger_search(parseInt(to));
}

//From read.js

jQuery(document).ready(function(){
    jQuery('.tooltipped').tooltip();
    jQuery('select').formSelect();
    jQuery('.sort-tags-drop-button').dropdown({
        alignment: 'right'
    });
});

function words_set(){
    const slider = document.getElementById('words-slider');
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
    if (slider.noUiSlider){
        slider.noUiSlider.updateOptions({
            range
        });
        return;
    }
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
//Single Selects
jQuery("div[id$=-select].custom-modal").on('change','input[type=checkbox]',function() {
    const modal_elem = jQuery(this).parents('div[id$=-select].custom-modal')[0];
    const select_status = modal_elem.getAttribute('select_status') || '';
    const multiple = modal_elem.getAttribute('multiple') !== null;
    if (multiple === false && select_status === '' && this.checked === true){
        jQuery(modal_elem).find(`input[type=checkbox]:checked:not(.cross)`).prop("checked", false);
        this.checked = true;
    }
    this.className = select_status;
});
function load_tags_data(){
    const tags_data = JSON.parse(document.getElementById('tags_data').innerHTML);
    words_set();
    const tags = Object.keys(tags_data);
    for (let y = 0; y < tags.length; y++) {
        const tag_name = tags[y];
        set_tags_of(tag_name);
    }
}
function set_tags_of(tag_name, sort = 'count'){
    const _selected = get_selected(tag_name,'value');

    const tags_data = JSON.parse(document.getElementById('tags_data').innerHTML);
    const this_tag_ids = Object.keys(tags_data[tag_name]);
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
    let construct = '';
    for (let u = 0; u < this_tag_ids.length; u++) {
        const tag_id = this_tag_ids[u];
        const tag = tags_data[tag_name][tag_id];
        construct += `
        <label class="col s12 btn-hover" style="color:var(--text-color) !important;padding:10px;">
            <input type="checkbox" checkbox value="${tag_id}"/>
            <text>${tag.name} (${tag.count})</text>
        </label>
        `;
    }
    const this_elem = jQuery(`#${tag_name}-select .tags_list`)[0];
    this_elem.innerHTML = construct;
    set_selected(tag_name,_selected);
}
function filters_from_url(){
    const urlParams = new URLSearchParams(location.search);
    if (urlParams.get('search') !== null){
        document.getElementById('search').value = urlParams.get('search');
        M.updateTextFields();
    }
    if (urlParams.get('only') !== null){
        document.getElementById('category-only').checked = true;
    }
    if (urlParams.get('words') !== null){
        const slider = document.getElementById('words-slider');
        slider.noUiSlider.set(urlParams.get('words').split(','));
    }
    const multiselects = all_tax;
    for (let p = 0; p < multiselects.length; p++) {
        const included = urlParams.get(multiselects[p] + '_included') === null ? [] : urlParams.get(multiselects[p] + '_included').split(',');
        const excluded = urlParams.get(multiselects[p] + '_excluded') === null ? [] : urlParams.get(multiselects[p] + '_excluded').split(',');
        set_selected(multiselects[p],{
            included,
            excluded
        });
    }
}

load_tags_data();
filters_from_url();