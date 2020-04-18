<!-- New Post Form -->
<div id="new" class="modal modal-large">
	<form name="newbook" method="post" class="col s12" action="/wp-content/themes/book-writer/write/php/new-book.php">
		<div class="modal-content">
			<ul class="collapsible">
				<li class="active">
					<div class="collapsible-header">About</div>
					<div class="collapsible-body">
						<div class="row">
							<div class="input-field col s12">
								<textarea id="book_title-new" name="book_title" class="materialize-textarea"></textarea>
								<label for="book_title">Book Title*</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<textarea id="book_description-new" name="book_description" class="materialize-textarea" data-length="400"></textarea>
								<label for="book_description">Description</label>
							</div>
						</div>
						<div class="input-field"><span class="helper-text">If your fandom isn't listed, you can <a target="_blank" href="/fandom">create</a> it.</span></div>
						<div class="row">
							<div class="input-field col s12">
								<select class="validate" multiple style="display:none;" id ="category-new" name="category[]">
									<?php
										$terms = get_terms(array(
											'taxonomy' => 'category',
											'hide_empty' => false,
											'parent'    =>0
										));
										foreach ($terms as $term){
											echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
										}
									?>
								</select>
								<label>Category</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<select class="validate" data-limit="3" searchable="Search Fandoms" multiple style="display:none;" id ="fandom-new" name="fandom[]">
								<?php //options to be added with js ?>
								</select>
								<label>Fandom*</label>
								<span class="helper-text">Please select upto three fandoms for a crossover.</span>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="collapsible-header">Tags</div>
					<div class="collapsible-body">
						<?php $taxonomies = array('rating','language','status','genre');?>
						<?php $multi_taxonomies = array('genre');?>
						<?php $required_taxonomies = array('rating','language','status');?>
						<?php foreach ($taxonomies as $taxonomy){ ?>
							<div class="row">
								<div class="input-field col s12">
									<select searchable="Search <?php echo ucfirst($taxonomy); ?>" <?php if (in_array($taxonomy, $multi_taxonomies)){echo 'multiple';}?> style="display:none;" id="<?php echo $taxonomy; ?>-new" name="<?php echo $taxonomy; if (in_array($taxonomy, $multi_taxonomies)){echo '[]';} ?>">
										<?php if (in_array($taxonomy, $multi_taxonomies) == false){echo '<option value="" disabled selected>Please Select a ' . ucfirst($taxonomy) . '</option>';}?>
										<?php
										$terms = get_terms(array(
											'taxonomy' => $taxonomy,
											'hide_empty' => false,
										));
										foreach ($terms as $term){
											echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
										}
										?>
									</select>
									<label><?php echo ucfirst($taxonomy); if (in_array($taxonomy, $required_taxonomies)){echo '*';} ?></label>
								</div>
							</div>
						<?php } ?>
						<div class="row"><div class="col s12"><label>Characters</label></div></div>
						<div class="row">
							<div class="col s12" id="character_wrapper-new">
					            <div>
    							    <label>Choose your fandom to select characters.</label>
    								<input style="display:none;" name="character" id="character_input-<?php echo $book->ID; ?>" value="">
								</div>
							</div>
						</div>
						<div class="row"><div class="col s12"><label>Pairings</label></div></div>
						<div id="pairing-box_new">
						</div>
						<div class="row" id="pairing_btn_wrapper-new">
							<div class="col s12">
							    <label>Select your characters.</label>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<div class="chips" id="tags-new"></div>
								<input style="display:none;" name="tag" id="tags_input-new" value="">
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="collapsible-header">Extras</div>
					<div class="collapsible-body">
						<div class="row">
							<div class="input-field col s12">
								<textarea id="detailed_description-new" name="detailed_description" class="materialize-textarea"></textarea>
								<label for="detailed_description">Detailed Description</label>
							</div>
						</div>
						<div class="row">
							<div class="switch center-block" style="padding-left:10px;">
								<label>
									<label id="comments-label-new">Comments Enabled</label>
									<input onclick="change_comment_label('new')"  id='comments-new' name='comments' type='checkbox' value='open' checked>
									<span class='lever'></span>
								</label>
							</div>
						</div>
					</div>
				</li>
			</ul>
		</div>
	    <div class="modal-footer">
			<button style="z-index:0;" class="waves-effect waves-light btn-small" type="submit" href="#errorcard">Create</button>
		</div>
	</form>
</div>
<?php
	$terms_top_cat = get_terms(array(
		'taxonomy' => 'category',
		'parent'   => 0,
		'hide_empty' => false,
	));
	foreach($terms_top_cat as $term_c){
		echo '<optgroup style="display:none;" label="f-' . $term_c->slug . '">';
		$terms = get_terms(array(
			'taxonomy' => 'category',
			'hide_empty' => false,
			'parent' => $term_c-> term_id,
		));
		foreach ($terms as $term){
			echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
		}
		echo '</optgroup>';
	}
?>
<?php
	$terms_c = get_terms(array(
		'taxonomy' => 'category',
		'hide_empty' => false,
		'childless'=> true,
	));
	foreach($terms_c as $term_c){
		echo '<span style="display:none;" name="' . $term_c->name . '" label="' . $term_c->slug . '">';
		$terms = get_terms(array(
			'taxonomy' => 'character',
			'hide_empty' => false,
			'parent' => $term_c-> term_id,
		));
		foreach ($terms as $term){
			echo $term->name . ',';
		}
		echo '</span>';
	}
?>
<?php
	echo '<optgroup style="display:none;" id="get_tag_opts">';
	$terms = get_terms(array(
		'taxonomy' => 'tag',
		'hide_empty' => false,
	));
	foreach ($terms as $term){
		echo '<option value="' . $term->name . '"></option>';
	}
	echo '</optgroup>';
?>
<script type = "text/javascript">
	function addpairing(id){
		var i = jQuery("#pairing-box_" + id).children().length;

		var raw_datas = jQuery('[id ^=character_input_][id $=-' + id + ']');
		data = [];
		for (j = 0; j < raw_datas.length; j++) {
		    if(raw_datas[j].value != ''){
		        data = data.concat(raw_datas[j].value.split(','));
		    }
	    }
		var options = '';
		for (j = 0; j < data.length; j++) {
		    options += '<option value="' + data[j] +'">' + data[j] +'</option>';
	    }
		if (i < 10) {
			jQuery("#pairing-box_" + id).append('<div class="row pairing_cont"><div class="input-field col s12"><select searchable="Search Pairing" multiple style="display:none;" name="pairing_' + (i+1) + '-' + id + '[]">' + options + '</select><label>Pairing ' + (i+1) + '</label></div></div>').find("select").formSelect();
		}
	};
	function removepairing(id){
		
		var i = jQuery("#pairing-box_" + id).children().length;
		if (i > 0){
			jQuery("select[name=pairing_" + i + "-" + id + "\\[\\]]").parents('.pairing_cont').remove();
		}
	};
</script>