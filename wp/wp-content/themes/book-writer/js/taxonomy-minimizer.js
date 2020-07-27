////HTML is located in 'header-taxonomy' Template Part
//Script included in 'footer.php'

jQuery(document).ready
(
    function()
    {

		jQuery("#hide-category-button").click
		(
			function()
			{
			    if (this.className == 'fas fa-minus-square'){
					jQuery(this).attr("class","fas fa-plus-square");
					jQuery("#hide-category").get(0).style.setProperty("display", "none");
				}
				else if (this.className == 'fas fa-plus-square'){
					jQuery(this).attr("class","fas fa-minus-square");
					jQuery("#hide-category").get(0).style.setProperty("display", "inline");
				}
			    jQuery("#hide-category").text("<<");

			}
		);
	}
); 