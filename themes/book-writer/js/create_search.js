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
    jQuery('#' + group + '-select [type=checkbox]:not(:checked)').removeClass('cross');
    jQuery('#' + group + '-select').attr('select_status','');
    jQuery('#' + group + '-select' + ' .btn-include').addClass('active');
    jQuery('#' + group + '-select' + ' .btn-exclude').removeClass('active');
    
}
function trigger_exclude(group){
    jQuery('#' + group + '-select [type=checkbox]:not(:checked)').addClass('cross');
    jQuery('#' + group + '-select').attr('select_status','cross');
    jQuery('#' + group + '-select' + ' .btn-include').removeClass('active');
    jQuery('#' + group + '-select' + ' .btn-exclude').addClass('active');
}
function clear_checkboxes(group){
    jQuery('#' + group + '-select [type=checkbox]:checked').prop("checked", false);
}
jQuery("div[id$=-select].custom-modal [type=checkbox]").change(function() {
    if (this.checked == false){
        this.className = jQuery(this).parents('div[id$=-select].custom-modal').attr('select_status');
    }
});
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
    return {included:include,excluded:exclude};
    
}

function reset_settings(){
    var tax = ['genre','character','pairing','tag','fandom'];
    for (var i = 0; i < tax.length; i++) {
        clear_checkboxes(tax[i]);
        jQuery('#' + tax[i] + '_show .chips_s').children().remove();
        jQuery('#' + tax[i] + '_show .all_label').children().remove();
        jQuery('#' + tax[i] + '_show .all_label').append('<div class="grey-text">All</div>');
    }
    var tax = ['rating','language','status'];
    for (var i = 0; i < tax.length; i++) {
        jQuery('#' + tax[i]).val('');
    }
    jQuery('#sort').val('modified/DESC');
    jQuery('#search').val('');
    slider.noUiSlider.reset();
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
    var elems = jQuery('[id$="-select"]');
    for (var i = 0; i < elems.length; i++) {
        if (jQuery(elems[i]).css('display') == 'block'){
            var this_elem = elems[i];
            break;
        }
    }
    var group = this_elem.id.replace('-select','');
    var selected = get_selected(group,'name');
    jQuery('#' + group + '_show .chips_s').children().remove();
    jQuery('#' + group + '_show .all_label').children().remove();
    if (selected.included.length == 0 && selected.excluded.length == 0){
        jQuery('#' + group + '_show .all_label').append('<div class="grey-text">All</div>');
    }
    for (var i = 0; i < selected.included.length; i++) {
        jQuery('#' + group + '_show .included_chips').append('<div class="chip">' + selected.included[i] + '</div>');
    }
    for (var i = 0; i < selected.excluded.length; i++) {
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

    var words = slider.noUiSlider.get();
    words[0] = parseInt(words[0].replace(/[,]+/g,'').replace(' Words',''));
    words[1] = parseInt(words[1].replace(/[,]+/g,'').replace(' Words',''));
    words = words.join();
    var data = {
        ajax: 1,
        search_id: document.getElementById('search_id').innerHTML,
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
    var single_tax = ['rating','language','status'];
    for (var i = 0; i < single_tax.length; i++) {
        val = jQuery('#' + single_tax[i]).val();
        if (val != ''){
            data[single_tax[i] + '_included'] = val;
        }
    }
    var multi_tax = ['fandom','genre','character','pairing','tag'];
    var selected;
    for (var i = 0; i < multi_tax.length; i++) {
        selected = get_selected(multi_tax[i],'value');
        if (selected.included.length != 0){
            data[multi_tax[i] + '_included'] = selected.included.join();
        }
        if (selected.excluded.length != 0){
            data[multi_tax[i] + '_excluded'] = selected.excluded.join();
        }
    }
    jQuery.ajax({
        url: '/wp-content/themes/book-writer/php/search.php',
        type: 'post',
        data: data,
        dataType: 'JSON',
        success: function(response){
            if (response['output'] == 6){
                  M.toast({html: 'An error occured.'});
                  window.location.reload();
            }
            jQuery("#box").removeClass("center-align");
            jQuery("#search-btn").removeClass("disabled");
            var response_arr  = response;
            document.getElementById('search_id').innerHTML = response_arr['search_id'];

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

var slider = document.getElementById('words-slider');
var max = parseInt(document.getElementById("max_count").innerHTML);
noUiSlider.create(slider, {
    start: [0, max],
    connect: true,
    step: 100,
    orientation: 'horizontal', // 'horizontal' or 'vertical'
    range: {
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
    },
    format: wNumb({
        decimals: 0,
        thousand: ',',
        suffix: ' Words',
    }),
});
jQuery(document).ready(function(){
    jQuery('.tooltipped').tooltip();
    jQuery('select').formSelect();      
});

var urlParams = new URLSearchParams(location.search);
if (urlParams.get('search') !== null){
    document.getElementById('search').value = urlParams.get('search');
    M.updateTextFields();
}
if (urlParams.get('only') !== null){
    document.getElementById('category-only').checked = true;
}
if (urlParams.get('words') !== null){
    slider.noUiSlider.set(urlParams.get('words').split(','));
}
var singleselects = ['rating','language','status'];
for (var p = 0; p < singleselects.length; p++) {
    var get_ = urlParams.get(singleselects[p] + '_included');
    if (get_ !== null){
        jQuery('#' + singleselects[p]).val(get_);
    }
}
var multiselects = ['fandom','genre','character','pairing','tag'];
for (var p = 0; p < multiselects.length; p++) {
    trigger_custom_modal(multiselects[p] + '-select');
    
    close_custom_modal();
}