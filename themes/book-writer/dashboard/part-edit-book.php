<main>
	<?php
	//PHP - Preparation of data
	//Use <?= $var to echo in HTML
	$book_id = page_chain_arr[1];
	$book = get_post($book_id);
	$data = get_data($book_id);
	$jsondata = json_encode($data);
	?>
	<!-- HTML STARTS -->
	<span style="display:none;" id="jsondata"><?= $jsondata ?></span>
	<a onclick="load_page('write')" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-arrow-left"></i></a>
	<form name="editbook" method="post" class="col s12">
		<ul class="collapsible">
			<li class="active">
				<div class="collapsible-header">About</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
					        <input name="book_title" type="text" class="validate" data-length="40">
							<label for="book_title">Book Title*</label>
						</div>
					</div>
					<div class="row">
						<div class="input-field col s12">
        					<textarea name="book_description" class="materialize-textarea" data-length="400"></textarea>
							<label for="book_description">Description*</label>
						</div>
					</div>
					<div class="input-field">
						<span class="helper-text">
							If your fandom isn't listed, you can <a target="_blank" href="/fandom">create</a> it.
						</span>
					</div>
					<m-select
					props="
						name=category;
						searchable;
						multiple;
					"
					tag_type="category">
					</m-select>
					<div class="fandom-wrapper"></div>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Tags</div>
				<div class="collapsible-body">
					<!--     Rating       Language       Status       Genre   -->
					<?php $taxonomies = array('rating','language','status','genre');?>
					<?php $multi_taxonomies = array('genre');?>
					<?php $required_taxonomies = array('rating','language','status');?>
					<?php foreach ($taxonomies as $taxonomy){ ?>
						<m-select
						props="
							name=<?= $taxonomy ?>;
							searchable;
							<?php if(in_array($taxonomy,$multi_taxonomies)){echo 'multiple;';}
							if(in_array($taxonomy,$required_taxonomies)){echo 'required;';} ?>
						"
						tag_type="<?= $taxonomy ?>"
						></m-select>
					<?php } ?>

					<!-- Characters -->
					<div class="row"><div class="col s12"><label>Characters</label></div></div>
					<div class="character-wrapper"></div>

					<!-- Pairings -->
					<div class="row"><div class="col s12"><label>Pairings</label></div></div>
					<div class="pairing-wrapper"></div>
					<div class="row pairing-add" style="display:none;">
						<div class="col s12">
							<button type="button" style="display:flex;" class="btn-flat valign-wrapper">
								<span style="margin-right:5px;margin-bottom:2px;" class="material-icons">add</span>
								Add Pairing
							</button>
						</div>
					</div>

					<!-- Tags -->
					<m-chips
					props="
						name=tag;
						label=Tags;
					"
					>
					</m-chips>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Extras</div>
				<div class="collapsible-body">
					<!-- Detailed Description -->
					<div class="row">
						<div class="input-field col s12">
							<textarea name="detailed_description" class="materialize-textarea"><?= $detailed_description ?></textarea>
							<label for="detailed_description">Detailed Description</label>
						</div>
					</div>
					
					<!-- Anonymous Reviews -->
					<div class="switch center-block" style="padding-left:10px;">
						<label>
							<label></label>
							<input on_label="Anonymous Reviews" off_label="Anonymous Reviews" name="anonymous_reviews" type="checkbox">
							<span class="lever"></span>
						</label>
					</div>

				</div>
			</li>
		</ul>
		<!-- Publish -->
		<div class="switch center-block" style="padding-left:10px;">
			<label>
				<label></label>
				<input on_label="Publish" off_label="Draft" name="publish" type="checkbox">
				<span class="lever"></span>
			</label>
		</div>

		<div style="text-align:right;">
		<button class="waves-effect waves-light btn-small modal-trigger" id="chapters-trigger" data-target="select-chapters">Chapters</button>
        <div id="button-wrapper" style="display:inline-block;padding-right: 15px;padding-left: 15px;"><button id="submit-btn" class="waves-effect waves-light btn-small" type="submit">Save</button></div>
		</div>
		<div id="reCAPTCHA_div" style="margin-top:15px;" class="g-recaptcha" data-sitekey="6Lc_ROEUAAAAAE2WALbN67FKxK284OnW7jSxEBth"></div>

	</form>
	<!-- Modal Structure -->
	<div id="select-chapters" class="modal modal-large">
	<div class="modal-content">
	</div>

</div>
	<script type = "text/javascript">
		var data = JSON.parse(jQuery('#jsondata')[0].innerHTML);

		jQuery("form[name='editbook']").submit(function(event) {
			event.preventDefault();
			M.Toast.dismissAll();
			var data_submit = {};
			var error = false;
			//reCAPTCHA
			const reCAPTCHA = grecaptcha.getResponse();
			if (reCAPTCHA == ''){
				M.toast({html: 'Please confirm you are not a robot by verifying yourself.'});
				error = true;
			}


			data_submit.selected_chapters = get_selected_chapters();

			//data_submit collection
			data_submit.book_id = data.selected.book_id;
			data_submit.publish = jQuery('[name="publish"]')[0].checked;
			data_submit.anonymous_reviews = jQuery('[name="anonymous_reviews"]')[0].checked;
			data_submit.title = jQuery('[name="book_title"]').val();
			data_submit.description = jQuery('[name="book_description"]').val();
			data_submit.detailed_description = jQuery('[name="detailed_description"]').val();
			data_submit.category = category_class.get_selected();
			data_submit.fandom = fandom_class.get_selected();
			data_submit.characters = character_class.get_existing();
			data_submit.pairing = pairing_class.get_selected();
			data_submit.rating = forceArray(jQuery('[name="rating"]').val());
			data_submit.language = forceArray(jQuery('[name="language"]').val());
			data_submit.status = forceArray(jQuery('[name="status"]').val());
			data_submit.genre = _default(jQuery('[name="genre[]"]').val(),[]);
			data_submit.tag = m_chips.getData(jQuery('[name="tag"]')[0]);
			
			//Validation
			if(data_submit.publish == true) {
				if (
					data_submit.title == "" ||
					data_submit.title.length > 40 ||
					data_submit.description == "" ||
					data_submit.description.length > 400 ||
					data_submit.category.length < 1 ||
					data_submit.fandom.length < 1 ||
					data_submit.rating.length < 1 ||
					data_submit.language.length < 1 ||
					data_submit.status.length < 1
				){
					M.toast({html: 'Please fill all the required fields.'});
					error = true;
				}
				if (data_submit.selected_chapters.length < 1){
					M.toast({html: 'Select the chapters you want to publish.'});
					jQuery('#select-chapters').modal('open');
					error = true;
				}
			}
			if (error == true){
				return;
			}
			spin("#button-wrapper");
			api('submit_book',{
				dataType: 'JSON',
				reCAPTCHA: reCAPTCHA,
				data: data_submit,
				callback: function(response){
					grecaptcha.reset();
					M.Toast.dismissAll();
					document.getElementById("button-wrapper").innerHTML = '<button id="submit-btn" class="waves-effect waves-light btn-small" type="submit">Save</button>';
					
					if (response.code == 1){
						M.toast({html: 'Book Published.'});
					}
					else if (response.code == 2){
						M.toast({html: 'Book Saved.'});
					}
					else if (response.code > 5){
						M.toast({html: 'There was a problem saving your book.'});
					}
					else{
						M.toast({html: 'An unknown error occured.'});
					}
					if ((response.code <= 5) && data_submit.book_id == 'new'){
						window.location.href = document.location.origin + '/dashboard/write/' + response.book_id;
					}
				},
				error: function (first,second,third){
					console.log(arguments);
				}
			});
			
		});
		function get_selected_chapters(){
			//Chapters
			var all_chapters = jQuery('#select-chapters.modal input[type=checkbox]');
        	var selected = [];
			for (i = 0; i < all_chapters.length; i++) {
        	    if (all_chapters[i].checked == true){
        	        selected.push(all_chapters[i].value);
        	    }
			}
			return selected;
		}
		function initializeChapters(){
			var wrapper = jQuery('#select-chapters .modal-content')[0];
			jQuery(wrapper).children().remove();
			jQuery(wrapper).append('<div class="row"><h4>Select chapters to publish</h4></div>');
			var all_chapters = data.selected.chapters.published.concat(
				data.selected.chapters.draft
			);
			if (all_chapters.length <= 0){
				jQuery(wrapper).append('<label>No chapters.</label>');
				return;
			}
			jQuery(wrapper).append('<table class="highlight"><thead><tr><th style="width:10%">Sort</th><th style="width:10%">Publish</th><th style="width:80%">Chapter</th></tr></thead><tbody class="chapters-list"></tbody></table>');
			var chapter_construct = '';
			for (let i = 0; i < all_chapters.length; i++) {
				let chapter = all_chapters[i];
				chapter_construct += '<tr style="cursor:pointer;"><td><span class="material-icons">drag_indicator</span></td><td class="center-align"><label><input type="checkbox" value="';
				chapter_construct += chapter.ID;
				chapter_construct += '" ';
				chapter_construct += _default(chapter.publish_allowed,' disabled ');
				chapter_construct += _default(data.selected.chapters.draft.includes(chapter),' checked ');
				chapter_construct += '/><span></span></label></td><td><span>';
				chapter_construct += chapter.title;
				chapter_construct += '</span></td></tr>';
			}
			var chapters_list = jQuery('#select-chapters .chapters-list')[0];
			jQuery(chapters_list).append(chapter_construct);
			const sortable = new Draggable.Sortable(
				jQuery(chapters_list)[0], {
					draggable: 'tr',
					handle: '.material-icons',
					distance: 10
				}
			)
		}
		function parseDataForInput(dataToParse){
			options = [];
			//Prepare options array of objects
			for (let j = 0; j < Object.keys(dataToParse).length; j++) {
				let value = parseInt(Object.keys(dataToParse)[j]);
				let label = dataToParse[value].name;
				options.push({label,value});
			}
			return options;
		}
		const pairing_class = class pairing_class {
			constructor(){ }
			static change(){

			}
			static get_selected(){
				var pairings_all = {};
				var pairing_elems = jQuery('.pairing');
				for (let i = 0; i < pairing_elems.length; i++) {
					let pairing_arr = _default(jQuery(pairing_elems[i]).val(),[]);
					pairings_all[i + 1] = pairing_arr;
				}
				return pairings_all;
			}
			static get_options(){
				var all_fandoms = merge_object_array(object_dive(data.all.categories,'fandoms'));
				var options = {optgroups: []};
				var character_chips = jQuery('.character-chips');
				for (let i = 0; i < character_chips.length; i++) {
					let chip = character_chips[i];
					let fandom_characters_selected = m_chips.getData(chip);
					let characters_proper = [];
					for (let i = 0; i < fandom_characters_selected.length; i++) {
						let char = fandom_characters_selected[i];
						characters_proper.push({
							label: char,
							value: char
						});
					}
					let fandom_id = chip.getAttribute('name');
					let fandom_name = all_fandoms[fandom_id].name;
					options.optgroups.push({
						label: fandom_name,
						value:fandom_id,
						options: characters_proper
					});
				}
				return options;
			}
			static restartAll(){
				var pairings_all = pairing_class.get_selected();
				var pairing_options = pairing_class.get_options();
				var wrapper = jQuery('.pairing-wrapper')[0];
				jQuery(wrapper).children().remove();
				
				for (let i = 0; i < Object.keys(pairings_all).length; i++) {
					let pairing_num = String(Object.keys(pairings_all)[i]);
					let pairing = pairings_all[pairing_num];					

					let pairing_inst = pairing_class.add();
					pairing_inst.select(pairing);
				}
			}
			static add(){
				var x = jQuery('.pairing-wrapper').children().not('label').length + 1;

				var pairing_inst = new m_select({
					label: 'Pairing ' + x,
					multiple: true,
					width:'s10 m11',
					after:'<div class="col s2 m1 remove-pairing">' +
					'<button type="button" class="btn-floating btn-hover">' +
					'<i style="color: var(--text-color);" class="material-icons">remove</i>' +
					'</button>' +
					'</div>'
				},{
					class: ' pairing '
				},pairing_class.get_options());
				jQuery('.pairing-wrapper').append('<m-select id="pairing_' + x + '"></m-select>');
				pairing_inst.replace(document.getElementById('pairing_' + x));
				return pairing_inst;
			}
		}
		const character_class = class character_class {
			constructor(){ }
			static change() {
				pairing_class.restartAll();

				if (character_class.get_all().length > 1){
					jQuery('.pairing-add').css('display','block');
				}
				else{
					jQuery('.pairing-add').css('display','none');
					jQuery('.pairing-wrapper').children().remove();
					jQuery('.pairing-wrapper').append('<label>Select your characters</label>');
				}
			}
			static get_all(type = 'object'){
				var char_of = jQuery('.character-chips');
				var full_chars = [];
				for (let i = 0; i < char_of.length; i++) {
					var instance = M.Chips.getInstance(char_of[i]);
					full_chars = full_chars.concat(instance.chipsData);
				}
				if (type == 'array'){
					full_chars_arr = [];
					for (let i = 0; i < full_chars.length; i++) {
						let element = full_chars[i].tag;
						full_chars_arr.push(element);
					}
					return full_chars_arr;
				}
				return full_chars;
			}
			static get_existing(){
				var character_elems = jQuery('.character-chips');
				var all_characters = {};
				for (let i = 0; i < character_elems.length; i++) {
					let elem = character_elems[i];
					let fandom_id = elem.getAttribute('name');
					let characters = m_chips.getData(elem);
					all_characters[fandom_id] = characters;
				}
				return all_characters;
			}
			static get_options(){
				var fandom_ids = _default(jQuery('[tag_type=fandom]').val(),[]);
				var fandoms = {};
				for (let i = 0; i < fandom_ids.length; i++) {
					let fandom_id = fandom_ids[i];
					let category_id = jQuery('[tag_type=fandom] option[value=' + fandom_id + ']').parent('optgroup').attr('value');
					let characters = data.all.categories[category_id].fandoms[fandom_id].characters;
					let characters_arr = object_attribute_array(characters,'name');
					fandoms[fandom_id] = characters_arr;
				}
				return fandoms;
			}
			static restartAll(){
				//First, get already existing characters sorted by fandoms
				var existing_characters = character_class.get_existing();
				var fandom_characters = character_class.get_options();
				var wrapper = jQuery('.character-wrapper');
				jQuery(wrapper).children().remove();

				for (let i = 0; i < Object.keys(fandom_characters).length; i++) {
					let fandom_id = Object.keys(fandom_characters)[i];
					let fandom_name = jQuery('[tag_type=fandom] option[value=' + fandom_id + ']')[0].innerHTML;
					let characters = fandom_characters[fandom_id];

					let character_inst = new m_chips({label: fandom_name,name: fandom_id},{class: 'character-chips'});
					jQuery(wrapper).append('<m-chips></m-chips>');
					let toReplace = jQuery(wrapper).children('m-chips')[0];
					character_inst.replace(
						toReplace,
						{
							autocomplete: characters,
							onChange: character_class.change
						}
					);
					character_inst.addData(_default(existing_characters[fandom_id],[]));
				}
			}
			static set(fandom_id,characters){
				var fandom_elem = jQuery('.character-wrapper [name=' + fandom_id +']')[0];
				m_chips.addData(fandom_elem,characters);
			}
		}
		const fandom_class =  class fandom_class {
			constructor(){ }
			static get_selected(){
				return _default(jQuery('[tag_type=fandom]').val(),[])
			}
			static change (){
				character_class.restartAll();
				character_class.change();
			}
			static get_options(){
				var categories_selected = category_class.get_selected();
				var input_data = parseDataForInput(data.all.categories);
				var options = {optgroups: []};
				for (let i = 0; i < input_data.length; i++) {
					let id = parseInt(input_data[i].value);
					let label = input_data[i].label;
					if (categories_selected.includes(id)){
						options.optgroups.push({
							label,
							value:id,
							options:parseDataForInput(data.all.categories[id].fandoms)
						});
					}
				}
				return options;
			}
			static restart(){
				var selected = _default(jQuery('[tag_type=fandom]').val(),[]);
				var wrapper = jQuery('.fandom-wrapper')[0];
				jQuery(wrapper).children().remove();
				jQuery(wrapper).append('<label>Choose a category to select fandoms.</label>');
				var options = fandom_class.get_options();
				//Same thing if we called category_class.get_selected().length
				if (options.optgroups.length > 0){
					var toReplace = jQuery('.fandom-wrapper label')[0];
					var fandom_inst = new m_select({
						name:'fandom',
						multiple: true,
						searchable: true
					},{tag_type: 'fandom'},options);
					fandom_inst.replace(toReplace);
					fandom_inst.select(selected);
				}
			}
			static set(values_array){
				m_select.select(jQuery('[tag_type=fandom]')[0],values_array);
			}
		}
		const category_class = class category_class {
			constructor(){ }
			static change(){
				fandom_class.restart();
				fandom_class.change();
			}
			static get_selected(){
				return _default(jQuery('[tag_type=category]').val(),[]).map(
					(element) => parseInt(element)
				);
			}
			static get_options(){
				return parseDataForInput(data.all.categories);
			}
			static initialize(){
				var elem = jQuery('[tag_type=category]')[0];
				var inst = new m_select(
					get_props(elem),
					getAllAttributes(elem),
					{options: category_class.get_options()}
				);
				inst.replace(elem);
				inst.select(Object.keys(data.selected.category));
				category_class.change();
			}
		}
		//Run to initialize data of tags

		//Make non-existent empty objects
		var all_taxonomies = ['rating','language','status','genre','tag'];
		for (let i = 0; i < all_taxonomies.length; i++) {
			data['all'][all_taxonomies[i]] = _default(data['all'][all_taxonomies[i]],{});
		}

		//Convert empty objects to empty arrays
		var all_selected = Object.keys(data.selected);
		for (let i = 0; i < all_selected.length; i++) {
			let element = all_selected[i];
			if (data.selected[element] instanceof Array){
				data.selected[element] = {};
			}
		}

		//Events
		jQuery(document).on('change','[tag_type=category]', category_class.change);
		jQuery(document).on('change', '[tag_type=fandom]',fandom_class.change);
		jQuery(document).on('click','.pairing-add button',pairing_class.add);
		jQuery(document).on('click','.remove-pairing button',function(){
			//Remove this pairing
			var wrapper = jQuery(this).parents('.remove-pairing').parent('.row');
			jQuery(wrapper).remove();
			//Restart
			pairing_class.restartAll();
		});


		//Initialize from selected
		
		//Tags - Rating Language Status Genre
		var taxonomies = ['rating','language','status','genre'];
		for (let i = 0; i < taxonomies.length; i++) {
			let taxonomy = taxonomies[i];
			let elem = jQuery('[tag_type=' + taxonomy + ']')[0];
			let options = parseDataForInput(data.all[taxonomy]);
			let tax_obj = new m_select(get_props(elem),getAllAttributes(elem),{options});
			tax_obj.replace(elem);
			tax_obj.select(Object.keys(data.selected[taxonomy]));
		}
		//Tags - Tag
		var elem = jQuery('m-chips')[0];	
		var tag = new m_chips(get_props(elem),getAllAttributes(elem));
		tag.replace(
			elem,
			{
				autocomplete: data.all.tag,
				set: Object.values(data.selected.tag),
			}
		);

		//Category Initialize
		category_class.initialize();
		
		//Fandom Initialize
		fandom_class.set(Object.keys(data.selected.fandom));
		
		//Character Initialize
		var all_fandom_characters = merge_object_array(object_dive(data.all.categories,'fandoms'));
		var all_selected_characters = Object.keys(data.selected.character);
		for (let i = 0; i < Object.keys(all_fandom_characters).length; i++) {
			let fandom_id = Object.keys(all_fandom_characters)[i];
			var selected_fandom_characters = object_attribute_array(
				filter_object(
					all_fandom_characters[fandom_id].characters,
					all_selected_characters
				),'name');
			if (selected_fandom_characters.length > 1){
				character_class.set(
					fandom_id,
					selected_fandom_characters
				);
			}
		}

		//Pairing Initialize
		for (let i = 0; i < Object.values(data.selected.pairing).length; i++) {
			let pairing = Object.values(data.selected.pairing)[i];
			let pairing_inst = pairing_class.add();
			pairing_inst.select(pairing);
		}

		//Text input Intializations
		jQuery('[name="book_title"]').val(data.selected.title);
		jQuery('[name="book_description"]').val(data.selected.description);
		jQuery('[name="detailed_description"]').val(data.selected.detailed_description);

		//Switch Initializations
		jQuery('[name="anonymous_reviews"]')[0].checked = data.selected.false;
		jQuery('[name="publish"]')[0].checked = data.selected.publish;
		jQuery('input[type="checkbox"][on_label][off_label]').trigger('change');

		//Chapters Initializations
		initializeChapters();

		//Materialize Initializations
		jQuery('.modal').modal();
		jQuery('.collapsible').collapsible();
		jQuery('[data-tooltip]').tooltip();
		jQuery('textarea[data-length],input[data-length][type="text"]').characterCounter();


    </script>
</main>