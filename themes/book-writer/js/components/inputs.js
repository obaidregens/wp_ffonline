function getAllAttributes(elem,except = []){
    var _return = {};
    for (let i = 0; i < elem.attributes.length; i++) {
        if (! except.includes(elem.attributes[i].name)){
            _return[elem.attributes[i].name] = elem.attributes[i].value;
        }
    }
    return _return;
}
function get_props(elem){
    var _return = [];
    elem.getAttribute('props').split(';').filter(value => value != '').forEach(function(value,index){
        value = value.trim();
        if (value != "" && typeof value !== 'undefined'){
            if (value.includes('=')){    
                let value_arr = value.split('=');
                _return[value_arr[0]] = value_arr[1];
            }
            else{
                _return[value] = true;
            }
            _return
        }
    });
    return _return;
}
// (array) options
function construct_options(options){
    construct = '';
    for (let b = 0; b < options.length; b++) {
        construct += '<option value="' + options[b].value + '">' + options[b].label  + '</option>';
    }
    return construct;
}
function deconstruct_options(options_construct){
    options_construct = jQuery(options_construct);
    options = [];
    for (let i = 0; i < options_construct.length; i++) {
        let option = options_construct[i];
        options.push(
            {
                label: option.innerHTML,
                value: options.getAttribute('value')
            }
        );        
    }
    return options;
}
const m_select = class m_select {
    constructor(props = {}, attributes = {},options = {options: []}) {
        if (props.label && ! props.name){
            props.name = props.label.toLowerCase();
        }
        if (props.name && ! props.label){
            props.label = props.name.charAt(0).toUpperCase() + props.name.substring(1);
        }
        if (! props.label){
            throw 'Props label or name are not given';
        }

        //Options Construct
        //Options Pattern -> options param -> {(array)options} -> {(string)label,(string,int)value}
        //Optgroups Pattern -> options param -> {(array)optgroups} -> {label,value,(array)options} -> {(string)label,(string,int)value}
        var options_construct = '';
        if (options.optgroups){
            for (let i = 0; i < options.optgroups.length; i++) {
                let optgroup = options.optgroups[i];
                options_construct += '<optgroup label="' + optgroup.label + '" value="' + optgroup.value + '">';
                options_construct += construct_options(optgroup.options);
                options_construct += '</optgroup>'
                
            }
        }
        else if (options.options){
            options_construct += construct_options(options.options);
        }
        else if (options.construct){
            options_construct += options.construct;
        }
        this.options_construct = options_construct;
        var select_construct = '<select ';
        if (props.searchable){
            select_construct += ' searchable="Search ' + props.label + '" ';
        }
        if (props.multiple){
            select_construct += ' multiple ';
        }
        select_construct += ' name="' + props.name;
        if (props.multiple){
            select_construct += '[]';
        }
        select_construct += '" ';
        
        select_construct += '>';
        if (! props.multiple){
            select_construct += '<option value="" disabled>Please Select a ' + props.label + '</option>';
        }
        select_construct += '</select>';
        var label_construct = '<label>' + props.label;
        if (props.required){
            label_construct += '*';
        }
        label_construct += '</label>';
        attributes.class = _default(attributes.class,'') + ' m-select ';
        attributes.style = _default(attributes.style,'') + ';display:none;';
        var after = _default(props.after,'');
        var before = _default(props.before,'');

        select_construct = jQuery(select_construct).attr(attributes)[0].outerHTML;
        var full_construct = '<div class="row">' + before + '<div class="input-field col ' + _default(props.width,'s12') + '">' + select_construct + label_construct + '</div>' + after + '</div>';
        this.elem = jQuery(full_construct)[0];
        this.props = props;
    }
    replace(oldElem){
        if (this.options_construct == ''){
            this.options_construct = jQuery(oldElem).html();
        }
        jQuery(oldElem).replaceWith(this.elem);
        this.DOM = jQuery("[name='" + this.props.name + "']")[0];
        if (typeof this.DOM === 'undefined'){
            this.DOM = jQuery('[name="' + this.props.name + '[]"]')[0];
        }
        jQuery(this.DOM).append(this.options_construct);
        jQuery(this.DOM).formSelect();
    }
    select(values){
        if (! this.DOM){
            throw 'Element hasn\'t been placed in DOM yet';
        }
        m_select.select(this.DOM,values);
    }
    static select(elem,values = []){
        values = values.map((value) => String(value));
        jQuery(elem).val(values);
        jQuery(elem).formSelect();
        jQuery(elem).trigger('change');
    }
}

const m_chips = class m_chips {
    constructor(props = {}, attributes = {}) {
        if (props.label && ! props.name){
            props.name = props.label.toLowerCase();
        }
        if (props.name && ! props.label){
            props.label = props.name.charAt(0).toUpperCase() + props.name.substring(1);
        }
        if (! props.label){
            throw 'Props label or name are not given';
        }
        var label_construct = '<label>' + props.label;
        if (props.required){
            label_construct += '*';
        }
        label_construct += '</label>';
        var chips_construct = '<div ';
        chips_construct += ' name="' + props.name + '" ';
        
        chips_construct += '></div>';
        attributes.class = _default(attributes.class,'') + ' m-chips chips ';
        chips_construct = jQuery(chips_construct).attr(attributes)[0].outerHTML;
        var full_construct = '<div class="row"><div class="col ' + _default(props.width,'s12') + '">' + label_construct + chips_construct + '</div></div>';
        this.elem = jQuery(full_construct)[0];
        this.props = props;
    }
    replace(oldElem,options = {}){
        var build_options = {
            placeholder: 'Enter a ' + this.props.label,
            secondaryPlaceholder: 'Enter',
        };
        if (options.autocomplete){
            build_options.autocompleteOptions = {data:{}};
            for (let i = 0; i < Object.values(options.autocomplete).length; i++) {
                let element = Object.values(options.autocomplete)[i].name;
                build_options.autocompleteOptions.data[element] = null;
            }
        }
        oldElem.replaceWith(this.elem);
        this.DOM = jQuery("[name='" + this.props.name + "']")[0];
        if (options.onChange){
            build_options.onChipAdd = options.onChange;
            build_options.onChipDelete = options.onChange;
            jQuery(this.DOM).change(options.onChange);   
        }
        jQuery(this.DOM).chips(build_options);
        if (options.set){
            this.addData(options.set);
        }
    }
    addData(options){
        if (! this.DOM){
            throw 'Element hasn\'t been placed in DOM yet';
        }
        m_chips.addData(this.DOM,options);
    }
    static addData(elem,rawData){
        var instance = M.Chips.getInstance(elem);
        var chipsData = [];
        for (let i = 0; i < rawData.length; i++) {
            let element = rawData[i];
            chipsData.push({tag: element});
        }
        instance.options.data = chipsData;
        jQuery(elem).chips(instance.options);
        if (instance.options.onChipAdd){
            jQuery(elem).change(instance.options.onChipAdd);
        }
        jQuery(elem).trigger('change');
    }
    static getData(elem){
        var chipsData = M.Chips.getInstance(elem).chipsData;
        var _return = [];
        for (let i = 0; i < chipsData.length; i++) {
            let element = chipsData[i].tag;
            _return.push(element);
        }
        return _return;
    }
}
