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
		<style>
		.shades-circles > div {
			width: 44px;
			cursor: pointer;
			height: 44px;
			display: inline-block;
			margin: 0 5px;
			border-radius: 50px;
			background-color: black;
			box-shadow: var(--text-color) 0 0 4px;
		}
		.shades-circles > div[color="peach"] {
			background-color: #edd1b0;
		}
		.fonts-select > div {
			cursor: pointer;
			user-select:none;
		}
		</style>
		<label><div class="section" style="padding-bottom:10px;"><h3>Shades</h3></div></label>
		<div class="shades-circles" style="
			max-width: 175px;
			margin: auto;
		">
			<div class="acs-btn" action="shade" color="peach"></div>
		</div>
		<label><div class="section" style="padding-bottom:10px;"><h3>Fonts</h3></div></label>
		<div class="fonts-select" style="
			max-width: 175px;
			margin: auto;
		">
			<div class="acs-btn" action="font" style="font-family: 'Varela Round', sans-serif;">Default</div>
			<div class="acs-btn" action="font" style="font-family: 'Open Sans', sans-serif;">Open Sans</div>
			<div class="acs-btn" action="font" style="font-family: 'Roboto', sans-serif;">Roboto</div>
			<div class="acs-btn" action="font" style="font-family: 'Montserrat', sans-serif;">Montserrat</div>
			<div class="acs-btn" action="font" style="font-family: 'Pangolin', cursive;">Pangolin</div>
			<div class="acs-btn" action="font" style="font-family: 'Raleway', sans-serif;">Raleway</div>
		</div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect btn-flat">OK</a>
    </div>
</div>
<?php
