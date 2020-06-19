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
    var boxes = jQuery('#' + modal_id + ' [type=checkbox] + span');
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
                exclude.push(jQuery(all[g]).siblings('span')[0].innerHTML);
            }
            else{
                include.push(jQuery(all[g]).siblings('span')[0].innerHTML);
            }            
        }
    }
    return {
        included: include,
        excluded: exclude
    };
    
}

function reset_settings(){
    var tax = all_tax;
    for (var i = 0; i < tax.length; i++) {
        clear_checkboxes(tax[i]);
        jQuery('#' + tax[i] + '_show .chips_s').children().remove();
        jQuery('#' + tax[i] + '_show .all_label').children().remove();
        jQuery('#' + tax[i] + '_show .all_label').append('<div class="grey-text">All</div>');
    }
    jQuery('#sort').val('modified/DESC');
    jQuery('#search').val('');
    const slider = document.getElementById('words-slider');
    words_set(2000000);
    slider.noUiSlider.set([0,2000000]);
}
function trigger_custom_modal(id){
    var elem = document.getElementById(id);
    var dsply = jQuery(elem).css('display');
    if (dsply == 'none'){
        jQuery(elem).css('display','block');
        jQuery('#custom_overlay').css('display','block');
    }
    else if (dsply == 'block'){
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
    for (let i = 0; i < selected.included.length; i++) {
        jQuery('#' + group + '_show .included_chips').append('<div class="chip">' + selected.included[i] + '</div>');
    }
    for (let i = 0; i < selected.excluded.length; i++) {
        jQuery('#' + group + '_show .excluded_chips').append('<div class="chip">' + selected.excluded[i] + '</div>');
    }
    jQuery(this_elem).css('display','none');
    jQuery('#custom_overlay').css('display','none');
    
}

function trigger_search(page = false){
    document.getElementById('box').innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
    document.getElementById('pagination-wrapper').innerHTML = '';   
    jQuery("#box").addClass("center-align");
    jQuery("#search-btn").addClass("disabled");
    search_settings('close');

    const slider = document.getElementById('words-slider');
    var words = slider.noUiSlider.get();
    words[0] = parseInt(words[0].replace(/[,]+/g,'').replace(' Words',''));
    words[1] = parseInt(words[1].replace(/[,]+/g,'').replace(' Words',''));
    words = words.join();
    var data = {
        words: words,
        sort: jQuery('#sort').val(),
    };
    if (page != false){
        data.page = page;
    }
    var val = jQuery('#search').val();
    if (val != ''){
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
    api('search',{
        data: {
            search: data,
            placeholder: document.getElementById('placeholder_data').innerHTML,
            prev: document.getElementById('prev_ss').innerHTML
        },
        dataType: 'JSON',
        callback: function(response){
            if (response['output'] == 6){
                  M.toast({html: 'An error occured.'});
                  window.location.reload();
            }
            jQuery("#box").removeClass("center-align");
            jQuery("#search-btn").removeClass("disabled");
            const response_arr  = response;
            document.getElementById('prev_ss').innerHTML = response_arr['prev'];
            document.getElementById('tags_data').innerHTML = JSON.stringify( response_arr['tags_data'] );
            document.getElementById('pagination-wrapper').innerHTML = response_arr.paginate;

            document.getElementById('box').innerHTML = response_arr['output'];
            var construct = '?';
            var keys = Object.keys(data);
            for (var i = 0; i < keys.length; i++) {
                if (keys[i] == 'ajax' || keys[i] == 'search_id'){
                    continue;
                }
                construct += keys[i] + '=' + data[keys[i]];
                if (i < keys.length-1){
                    construct += '&';
                }
            }
            if (page == false){
                construct += '&page=1';
            }
            window.history.pushState("object or string", document.getElementsByTagName("title")[0].innerHTML,construct);
            jQuery('.dropdown-trigger').dropdown();
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
});

function words_set(max){
    const slider = document.getElementById('words-slider');
    const range = {
        'min': [0],
        '10%': [4000],
        '20%': [9000],
        '30%': [15000],
        '40%': [25000],
        '50%': [50000],
        '60%': [100000],
        '70%': [200000],
        '80%': [500000],
        '90%': [1000000],
        'max': [max]
    };
    if (slider.noUiSlider){
        slider.noUiSlider.updateOptions({
            range
        });
        return;
    }
    noUiSlider.create(slider, {
        start: [0, max],
        connect: true,
        step: 100,
        orientation: 'horizontal',
        range,
        format: wNumb({
            decimals: 0,
            thousand: ',',
            suffix: ' Words',
        }),
    });
}
function load_tags_data(){
    const tags_data = JSON.parse(document.getElementById('tags_data').innerHTML);
    words_set(parseInt(tags_data.max_words));
    const tags = Object.keys(tags_data);

    for (let y = 0; y < tags.length; y++) {
        const tag_name = tags[y];
        if (tag_name === 'max_words'){
            continue;
        }
        const this_tag_ids = Object.keys(tags_data[tag_name]);
        let construct = '';
        for (let u = 0; u < this_tag_ids.length; u++) {
            const tag_id = this_tag_ids[u];
            const tag = tags_data[tag_name][tag_id];
            construct += `
            <label class="col s12 btn-hover" style="color:var(--text-color) !important;padding:10px;">
            <input type="checkbox" value="${tag_id}"/>
            <span>${tag.name} (${tag.count})</span>
            </label>
            `;
        }
        jQuery(`#${tag_name}-select .tags_list`).children().remove();
        jQuery(`#${tag_name}-select .tags_list`).append(construct);
    }
    jQuery("div[id$=-select].custom-modal input[type=checkbox]").change(function() {
        const modal_elem = jQuery(this).parents('div[id$=-select].custom-modal')[0];
        const select_status = modal_elem.getAttribute('select_status') || '';
        const multiple = modal_elem.getAttribute('multiple') !== null;
        if (multiple === false && select_status === ''){
            jQuery(modal_elem).find(`input[type=checkbox]:checked:not(.cross)`).prop("checked", false);
            this.checked = true;
        }
        this.className = select_status;
    });
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
        
        const _included = urlParams.get(multiselects[p] + '_included') === null ? [] : urlParams.get(multiselects[p] + '_included').split(',');
        const _excluded = urlParams.get(multiselects[p] + '_excluded') === null ? [] : urlParams.get(multiselects[p] + '_excluded').split(',');
        
        const _mixed = _included.concat(_excluded);
        for (let m = 0; m < _mixed.length; m++) {
            const elem_ = jQuery(`#${multiselects[p]}-select [type="checkbox"][value="${_mixed[m]}"]`)[0];
            jQuery(elem_).prop('checked',true);
            if (_excluded.includes(_mixed[m])){
                jQuery(elem_).addClass('cross');
            }
        }
        trigger_custom_modal(multiselects[p] + '-select');
        close_custom_modal();
    }
}
load_tags_data();
filters_from_url();