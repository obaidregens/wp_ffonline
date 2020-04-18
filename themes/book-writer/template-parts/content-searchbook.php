<div class="fixed-action-btn" style="top:75px;">
    <a class="btn-floating btn-small modal-trigger" style="background-color:var(--theme-color);" href="#searchbook">
        <i class="fas fa-search"></i>
    </a>
</div>

<!--Search Book-->
<div id="searchbook" class="modal modal-large">
    <div class="modal-content">
      <div class="row">
        <div class="input-field col s12">
            <i class="fas fa-search prefix"></i>
          <input id="search_book" type="text">
          <label for="search_book">Search Book</label>
        </div>
      </div>
        <div id="load_search_results">
			Start typing to search...
        </div>
    </div>
</div>
<style>
	.search-item:hover span{
		background-color:transparent !important;
		background:linear-gradient(to bottom, #fafafa 10px, transparent 20px);
	}
</style>
<script>
jQuery("#search_book").keyup(function(event){
	var invalidKeys = [13,38,40,39,37,33,34,16,17,255,18,20,9];
	if (invalidKeys.indexOf(event.keyCode) != -1){
		return;
	}
    if ((event.ctrlKey && event.keyCode === 70)) {
        jQuery('#searchbook').modal('close');
        event.preventDefault();
        return;
    }
	jQuery('#load_search_results')[0].innerHTML = '<div class="center-align"><div class="preloader-wrapper big active"><div class="spinner-layer"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div>';
	jQuery.ajax({
		url: '/wp-content/themes/book-writer/php/search_book_contents.php',
		type: 'post',
		data: {ajax: 1,chapter_id:jQuery('#chapter_id').val(),s:jQuery('#search_book').val()},
		success: function(response){
			jQuery('#load_search_results')[0].innerHTML = response;
		}
	});    
});
</script>
<?php
