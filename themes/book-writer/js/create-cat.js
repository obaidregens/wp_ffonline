jQuery(document).ready(function(){
    jQuery('select').formSelect();
    var value;
  });
jQuery("#fandom").keyup(function(){
    value = jQuery("#fandom").val();
    var cat_val = jQuery("#category").val();
    var opts = jQuery('#' + cat_val).children();
    var opts_arr = [];		
    for (var j = 0; j < opts.length; j++) {
        var opt = jQuery(opts[j]).val().toLowerCase();
        opts_arr.push(opt);
    }
    var inarr = jQuery.inArray(value.toLowerCase(),opts_arr);
    if (value == '' || inarr != -1){
        jQuery('#submit_btn').prop("disabled", true);
    }
    else{
        jQuery('#submit_btn').prop("disabled", false);
    }
});
jQuery("#category").change(function(){
    jQuery('#fandom').prop("disabled", false);
    var cat_val = jQuery("#category").val();
    var opts = jQuery('#' + cat_val).children();
    var opts_obj = {};		
    for (var j = 0; j < opts.length; j++) {
        
        var opt = jQuery(opts[j]).val();
        opts_obj[opt] = null;
    }
    var elem = jQuery('#fandom');
    var instance = M.Autocomplete.getInstance(elem);
    instance.updateData(opts_obj);
});
jQuery(document).ready(function(){
 var data_obj = {};
jQuery('input.autocomplete').autocomplete({
  data: data_obj,
  onAutocomplete: function(val) {
    jQuery("#fandom").val(value);
    var elem = jQuery('#fandom');
    var instance = M.Autocomplete.getInstance(elem);
    instance.open();
  },
});
});
jQuery("#fandom_form").submit(function(event) {
    event.preventDefault();
    document.getElementById("button-wrapper").innerHTML = '<div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div>';
    var fandom = jQuery("#fandom").val();
    var category = jQuery('#category').val();
    jQuery.ajax({
        url: '/wp-content/themes/book-writer/php/create_fandom.php',
        type: 'post',
        data: {ajax: 1,fandom:fandom,category:category},
        success: function(response){
            document.getElementById("button-wrapper").innerHTML = '<button id="submit_btn" class="waves-effect waves-light btn-small" type="submit">Create Fandom</button>';
            if (response != '1'){
                M.toast({html: 'Fandom was added.'});
            }
            else{
                M.toast({html: "Fandom couldn't added. Refresh the page and try again."});
            }
        }
    });
});
