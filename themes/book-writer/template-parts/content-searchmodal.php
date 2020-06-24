<div id="search-settings" style="display:none;position:absolute;top:0;left:0;width:100%;background-color:var(--background-color);z-index:1000">
    <div id="custom_overlay" onclick="close_custom_modal();" style="display:none;z-index: 1001;opacity: 0.5;position: fixed;width: 100%;height: 100%;left: 0;top: 0;background-color: black;"></div>
    <a style="position:fixed;top:0;right:0;" onclick="search_settings('close');" class="btn-hover btn-floating btn-small waves-effect waves-light"><i class="fas fa-times"></i></a>
	<form name="search" class="col s12">
		<div style="padding:24px;">
			<div class="row">
				<div class="input-field col s12">
					<input name="search" id="search" type="text">
					<label for="search">Search</label>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<label>Sort</label>
					<select class="browser-default" id="sort" name="sort">
						<option value="modified/DESC">Last Updated</option>
						<option value="date/DESC">Book Published</option>
						<option value="favorites/DESC">Favorites</option>
						<option value="words/DESC">Words</option>
					</select>
				</div>
			</div>
			<?php $taxonomies = array('fandom','rating','language','status','genre','character','pairing','tag'); ?>
			<?php $tax_labels = array('Fandom','Rating','Language','Status','Genre','Characters','Pairings','Tags'); ?>
			<?php $multi_taxonomies = array('fandom','genre','character','pairing','tag'); ?>
			<?php foreach ($taxonomies as $key => $taxonomy){ ?>
                <?php
                $label = $tax_labels[$key];
                ?>
				<div class="row" onclick="trigger_custom_modal('<?= $taxonomy; ?>-select')" id="<?= $taxonomy; ?>_show">
					<div class="col s12">
						<label><?= $label; ?></label>
						<div class="chips_s included_chips" style="margin-bottom:3px;">
							
						</div>
						<div class="chips_s excluded_chips">
							
						</div>
						<div class="all_label"><div class="grey-text">All</div></div>
					</div>
				</div>
				<div <?= in_array($taxonomy,$multi_taxonomies) ? ' multiple ' : ''; ?> class="custom-modal" id="<?= $taxonomy; ?>-select" >
				<div class="modal-header">
					<div>
						<span style="width:100%;">
							<button style="width:48%;" type="button" class="btn-include active" onclick="trigger_include('<?= $taxonomy; ?>')">Include</button>
							<button style="width:48%;" type="button" class="right btn-exclude" onclick="trigger_exclude('<?= $taxonomy; ?>')">Exclude</button>
						</span>
					</div>
					<div><input placeholder="Search" class="search-tags"/></div>
				</div>
				<div style="padding:0 24px;">
					<div class="row tags_list" style="margin-bottom:0;">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" onclick="clear_checkboxes('<?= $taxonomy; ?>');blur(this);" class="left btn-flat btn-clear">Clear</button>
					<button type="button" onclick="close_custom_modal();" class="btn">OK</button>
				</div>
				</div>
			<?php } ?>
			<div class="row" style="padding: 0 25px;"><div id="words-slider"></div></div>
		</div>
		<div style="padding:24px;text-align:right;">
			<button onclick="
				reset_settings();
				blur(this);
			" type="button" style="margin-right:10px;" class="left btn-flat btn-clear">Reset</button>
			<button onclick="
				search_settings('close');
			" type="button" style="margin-right:10px;" class="btn-flat btn-clear">Close</button>
			<button class="waves-effect waves-light btn-small" id="search-btn" type="submit">Search</button>
		</div>
	</form>
</div>