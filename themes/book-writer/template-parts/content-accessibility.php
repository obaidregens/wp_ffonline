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
		<div class="section"><h4>Reading Settings</h4></div>
		
		<div class="portion">
		    <i class="fas fa-font fa-lg"></i>
				<a id="increasefontsize" class="btn-floating blue"><i class="fas fa-plus"></i></a>
			<a id="decreasefontsize" class="btn-floating blue"><i class="fas fa-minus"></i></a>
	    </div>
	    <div class="portion">
			<i class="fas fa-bars fa-lg"></i>
			<a id="increaselineheight" class="btn-floating blue"><i class="fas fa-plus"></i></a>
			<a id="decreaselineheight" class="btn-floating blue"><i class="fas fa-minus"></i></a>
	    </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="modal-close waves-effect btn-flat">Close</a>
    </div>
</div>
<?php
