<?php
?>
<div class="fixed-action-btn" style="
	top: -5px;
">
    <a class="btn-floating btn-small modal-trigger" style="background-color:var(--theme-color);" href="#accessibility">
        <i class="fas fa-sliders-h"></i>
    </a>
</div>

<!--Accessibility Menu-->
<div id="accessibility" class="modal accessibility-menu" style="background-color:var(--background-color);">
    <div class="modal-content" style="text-align:center;">
		<div class="section"><h3>Reading Preferences</h3></div>
		<div style="
			max-width: 175px;
			margin: auto;
		">
			<div>
				<a inc="+" action="font-size" class="acs-btn"><i class="fas fa-plus"></i></a>
				<span class="acs-icon material-icons">
					format_size
				</span>
				<a inc="-" action="font-size" class="acs-btn"><i class="fas fa-minus"></i></a>
			</div>
			<div>
				<a inc="+" action="line-height" class="acs-btn"><i class="fas fa-plus"></i></a>
				<span class="acs-icon material-icons">
					format_line_spacing
				</span>
				<a inc="-" action="line-height" class="acs-btn"><i class="fas fa-minus"></i></a>
			</div>
			<div>
				<a inc="+" action="p-height" class="acs-btn"><i class="fas fa-plus"></i></a>
				<span class="acs-icon material-icons">
					height
				</span>
				<a inc="-" action="p-height" class="acs-btn"><i class="fas fa-minus"></i></a>
			</div>
			<div>
				<a inc="+" action="margin" class="acs-btn"><i class="fas fa-plus"></i></a>
				<span class="acs-icon width-icon material-icons">
					vertical_align_center
				</span>
				<a inc="-" action="margin" class="acs-btn"><i class="fas fa-minus"></i></a>
			</div>
		</div>
		<label for="autoscroll"><div class="section" style="padding-bottom:10px;"><h3>Auto Scroll</h3></div></label>
		<div style="
			max-width: 175px;
			margin: auto;
		">
		
			<div class="switch row">
				<label>
				Disable
				<input id="autoscroll" type="checkbox">
				<span class="lever"></span>
				Enable
				</label>
			</div>
			<div id="autoscroll-s-wrapper" style="display:none;">
				Seconds per page
				<input style="text-align:center;" id="autoscroll-s" type="number" min="10" max="480">
			</div>
		</div>
		<label for="theme_switch"><div class="section" style="padding-bottom:10px;"><h3>Theme</h3></div></label>
		<div style="
			max-width: 175px;
			margin: auto;
		">
			<div class="switch row">
				<label>
				Light
				<input id="theme_switch" type="checkbox">
				<span class="lever"></span>
				Dark
				</label>
			</div>
		</div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect btn-flat">OK</a>
    </div>
</div>
<?php
