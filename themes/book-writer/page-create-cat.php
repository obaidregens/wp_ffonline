<?php
/**
 * Template Name: Create Fandom
 */
 ?>
<?php
//page_header
get_header(); ?>

<?php if(is_user_logged_in()) { ?>
    
    <form name="fandom_form" id="fandom_form" class="mobile-margin">
        <div class="row">
            <p class="grey-text">Please take care not to add fandoms that already exist, you'll be shown fandoms that exist as you type to prevent duplicates.</p>
        	<div class="input-field col s12 m12 l6 xl6">
                <select style="display:none;" id="category">
                    <option value="" disabled selected>Choose your category</option>
                    <?php
                    $terms = get_terms(array(
                        'taxonomy' => 'category',
                        'parent' => 0,
                        'hide_empty' => false,
                    ));
                    foreach($terms as $term){
                        ?><option value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></option><?php
                    }
                    ?>
                </select>
                <label>Category</label>
        	</div>
        	<div class="input-field col s12 m12 l6 xl6">
                <input name="category" type="text" id="fandom" disabled class="autocomplete">
                <label for="fandom">Enter Fandom</label>
        	</div>
        </div>
        <div class="row section">
        <div id="button-wrapper" class="right"><button id="submit_btn" class="waves-effect waves-light btn-small" disabled type="submit">Create Fandom</button></div>
        </div>
    </form>
    <?php
    	$terms_c = get_terms(array(
    		'taxonomy' => 'category',
    		'parent'   => 0,
    		'hide_empty'=>false,
    	));
    	foreach($terms_c as $term_c){
    		echo '<optgroup style="display:none;" id="' . $term_c->term_id . '">';
    		$terms = get_terms(array(
    			'taxonomy' => 'category',
    			'parent' => $term_c-> term_id,
    			'hide_empty' => false,
    		));
    		foreach ($terms as $term){
    			echo '<option value="' . $term->name . '"></option>';
    		}
    		echo '</optgroup>';
    	}
    ?>
    <script>
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
    </script>
<?php } else {
	get_template_part( 'template-parts/content', 'login' );
} ?>
<?php get_footer(); ?>