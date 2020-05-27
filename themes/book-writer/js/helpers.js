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
function timestamp_to_local(timestamp){
    const time = new Date(timestamp * 1000);
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