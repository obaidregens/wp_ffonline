<div class="fixed-action-btn" style="top:40px;">
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
<?php
