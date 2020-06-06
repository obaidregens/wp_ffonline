<?php
?>
<div class="fixed-action-btn">
    <a class="btn-floating btn-small modal-trigger" style="background-color:var(--theme-color);" href="#accessibility">
        <i class="fas fa-sliders-h"></i>
    </a>
</div>

<!--Accessibility Menu-->
<div id="accessibility" class="modal accessibility-menu" style="background-color:var(--background-color);">
    <div class="modal-content" style="text-align:center;">
		<div class="section"><h4>Reading Preferences</h4></div>
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
    <div class="modal-footer">
        <a href="#" class="modal-close waves-effect btn-flat">Close</a>
    </div>
</div>
<?php
