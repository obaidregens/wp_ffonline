<?php
/**
 * Template Name: Create Fandom
 */
 ?>
<?php
//page_header
global $js_bundle;
$js_bundle = global_bundle('create-cat');
$js_bundle->add('create-cat');
$js_bundle->enqueue();

get_header(); ?>

<?php if (is_user_logged_in()) { ?>
    
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
<?php } else {
	get_template_part( 'template-parts/content', 'login' );
} ?>
<?php get_footer(); ?>