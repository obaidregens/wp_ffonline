function checkboxLabel(){
    if (this.checked == true){
        var label = this.getAttribute('on_label');
    }
    else{
        var label = this.getAttribute('off_label');
    }
    jQuery(this).siblings('label')[0].innerHTML = label;
}
jQuery(document).on('change','input[type="checkbox"][on_label][off_label]',checkboxLabel);
jQuery('input[type="checkbox"][on_label][off_label]').trigger('change');

function merge_object_array(array) {
    var _return = {};
    for (let pp = 0; pp < array.length; pp++) {
        Object.assign(_return,array[pp]);
    }
    return _return;
}
function object_dive(object, columnName) {
    return Object.keys(object).map(function(value) {
        return object[value][columnName];
    });
}
function array_unique(array){
    let to_return = [];
    for (let bb = 0; bb < array.length; bb++) {
        const element = array[bb];
        if (! to_return.includes(element)){
            to_return.push(element);
        }
    }
    return to_return;
}
function array_intersect(array,array1){
    var intersection = [];
    for (let ll = 0; ll < array.length; ll++) {
        let element = array[ll];
        if (array1.includes(element)){
            intersection.push(element);
        }
    }
    return intersection;
}
function array_diff(array,array1){
    return array.filter(function(element){
        if (! array1.includes(element)){
            return element;
        }
    });
}
function array_remove(array_from,values){
    return array_diff(array_from, values);
}

function _default(_var,_default){
    if (! _var){
        return _default;
    }
    return _var;
}
function delete_key(object,key){
    delete object[key];
    return object;
}
function filter_object(object,allowed_keys/*Array*/){
    var _return = {};
    for (let i = 0; i < Object.keys(object).length; i++) {
        let key = Object.keys(object)[i];
        if (allowed_keys.includes(key)){
            _return[key] = object[key];
        }
    }
    return _return;
}
function object_attribute_array(object_or_array,attribute){
    var type = object_or_array.constructor.name;
    var _return = [];
    if (type == 'Object'){
        object_or_array = Object.values(object_or_array);
    }
    for (let i = 0; i < object_or_array.length; i++) {
        let element = object_or_array[i];
        _return.push(element[attribute]);
    }
    return _return;
}
function forceArray(variable){
    if (variable === null){
        return [];
    }
    var type = variable.constructor.name;
    if (type == 'Array'){
        return variable;
    }
    else if (type == 'Object'){
        return Object.values(variable);
    }
    else if (type == 'Number' || type == 'String' || type == 'Boolean'){
        return [variable];
    }
    else{
        return [];
    }
}
function spin(selector,align = false){
    var spinner =
    '<div class="preloader-wrapper big active">' + 
        '<div class="spinner-layer">' +
            '<div class="circle-clipper left">' +
                '<div class="circle"></div>' +
            '</div>' +
            '<div class="gap-patch">' + 
                '<div class="circle"></div>' +
            '</div>' +
            '<div class="circle-clipper right">' +
                '<div class="circle"></div>' +
            '</div>' +
        '</div>' +
    '</div>';
    jQuery(selector).html(spinner);
    if (align){
        jQuery(selector).css('text-align',align);
    }
}
function progress(state = true){
    if (state === true){
        jQuery('.progress').css('display','block');
        return;
    }
    jQuery('.progress').css('display','none');
}
function timestamp_to_local(timestamp){
    const time = new Date(timestamp * 1000);
    return time_obj_local(time);
}
function utc_dt_to_local(dt){
    const time = new Date(dt + ' UTC');
    return time_obj_local(time);
}
function time_obj_local(time){
    let time_formatted = time.toString();
    time_formatted = time_formatted.split(' GMT')[0];
    time_formatted = time_formatted.substring(0,time_formatted.length-3);
    return time_formatted;
}
function timestamp_duration(timestamp){
    var hours = Math.floor(timestamp / 60 / 60);
    var minutes = Math.floor(timestamp / 60) - (hours * 60);  
    var seconds = timestamp % 60;
  
    var formatted = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    return formatted;
  }
function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/////////preg_split function used in PHP
function preg_split (pattern, subject, limit, flags) {
    // http://kevin.vanzonneveld.net
    // + original by: Marco Marchi??
    // * example 1: preg_split(/[\s,]+/, 'hypertext language, programming');
    // * returns 1: ['hypertext', 'language', 'programming']
    // * example 2: preg_split('//', 'string', -1, 'PREG_SPLIT_NO_EMPTY');
    // * returns 2: ['s', 't', 'r', 'i', 'n', 'g']
    // * example 3: var str = 'hypertext language programming';
    // * example 3: preg_split('/ /', str, -1, 'PREG_SPLIT_OFFSET_CAPTURE');
    // * returns 3: [['hypertext', 0], ['language', 10], ['programming', 19]]
    // * example 4: preg_split('/( )/', '1 2 3 4 5 6 7 8', 4, 'PREG_SPLIT_DELIM_CAPTURE');
    // * returns 4: ['1', ' ', '2', ' ', '3', ' ', '4 5 6 7 8']
    // * example 5: preg_split('/( )/', '1 2 3 4 5 6 7 8', 4, (2 | 4));
    // * returns 5: [['1', 0], [' ', 1], ['2', 2], [' ', 3], ['3', 4], [' ', 5], ['4 5 6 7 8', 6]]

    limit = limit || 0; flags = flags || ''; // Limit and flags are optional

    var result, ret=[], index=0, i = 0,
        noEmpty = false, delim = false, offset = false,
        OPTS = {}, optTemp = 0,
        regexpBody = /^\/(.*)\/\w*$/.exec(pattern.toString())[1],
        regexpFlags = /^\/.*\/(\w*)$/.exec(pattern.toString())[1];
        // Non-global regexp causes an infinite loop when executing the while,
        // so if it's not global, copy the regexp and add the "g" modifier.
        pattern = pattern.global && typeof pattern !== 'string' ? pattern :
            new RegExp(regexpBody, regexpFlags+(regexpFlags.indexOf('g') !==-1 ? '' :'g'));

    OPTS = {
        'PREG_SPLIT_NO_EMPTY': 1,
        'PREG_SPLIT_DELIM_CAPTURE': 2,
        'PREG_SPLIT_OFFSET_CAPTURE': 4
    };
    if (typeof flags !== 'number') { // Allow for a single string or an array of string flags
        flags = [].concat(flags);
        for (i=0; i < flags.length; i++) {
            // Resolve string input to bitwise e.g. 'PREG_SPLIT_OFFSET_CAPTURE' becomes 4
            if (OPTS[flags[i]]) {
                optTemp = optTemp | OPTS[flags[i]];
            }
        }
        flags = optTemp;
    }
    noEmpty = flags & OPTS.PREG_SPLIT_NO_EMPTY;
    delim = flags & OPTS.PREG_SPLIT_DELIM_CAPTURE;
    offset = flags & OPTS.PREG_SPLIT_OFFSET_CAPTURE;

    var _filter = function(str, strindex) {
        // If the match is empty and the PREG_SPLIT_NO_EMPTY flag is set don't add it
        if (noEmpty && !str.length) {return;}
        // If the PREG_SPLIT_OFFSET_CAPTURE flag is set
        //      transform the match into an array and add the index at position 1
        if (offset) {str = [str, strindex];}
        ret.push(str);
    };
    // Special case for empty regexp
    if (!regexpBody){
        result=subject.split('');
        for (i=0; i < result.length; i++) {
            _filter(result[i], i);
        }
        return ret;
    }
    // Exec the pattern and get the result
    while (result = pattern.exec(subject)) {
        // Stop if the limit is 1
        if (limit === 1) {break;}
        // Take the correct portion of the string and filter the match
        _filter(subject.slice(index, result.index), index);
        index = result.index+result[0].length;
        // If the PREG_SPLIT_DELIM_CAPTURE flag is set, every capture match must be included in the results array
        if (delim) {
            // Convert the regexp result into a normal array
            var resarr = Array.prototype.slice.call(result);
            for (i = 1; i < resarr.length; i++) {
                if (result[i] !== undefined) {
                    _filter(result[i], result.index+result[0].indexOf(result[i]));
                }
            }
        }
        limit--;
    }
    // Filter last match
    _filter(subject.slice(index, subject.length), index);
    return ret;
}

function api(action,{data,callback,async = true,reCAPTCHA = null,reject}){
    return new Promise((res,rej) => {
        const options = {
            dataType: 'JSON',
            url: '/api',
            type: 'post',
            data: {action},
            async
        };
        if (reCAPTCHA === null){
            options.data.nonce = document.querySelector('nonce').innerHTML;
        }
        else{
            options.data.reCAPTCHA = reCAPTCHA;
        }
        if (typeof data === 'object'){
            options.data.data = data;
        }
        options.success = response => {
            res(response);
            if (callback instanceof Function){
                callback(response);
            }
        };
        options.error = response => {
            rej(response);
            if (reject instanceof Function){
                reject(response);
            }    
        };
        jQuery.ajax(options);
    });
}
jQuery('body').append(`
<div
id="full_backdrop_spinner"
style="
    background: black;
    width: 100%;
    display: none;
    z-index: 1000000;
    position: fixed;
    top: 0px;
    left: 0px;
    height: 100vh;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transform: scale(1.5);
    transition: opacity 0.5s;
    opacity: 0;
"
></div>
`);
function full_spin(show){
    if (show === false) {
        jQuery('#full_backdrop_spinner').css('opacity',0);
        setTimeout(function(){
            jQuery('#full_backdrop_spinner').css('display','none');
        },500);
    }
    else{
        jQuery('#full_backdrop_spinner').css('display','flex');
        jQuery('#full_backdrop_spinner').css('opacity',1);
        spin('#full_backdrop_spinner');
    }
}
function htmlspecialchars(text){
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });  
}