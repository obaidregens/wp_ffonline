<?php
function new_chapter($book){
?>
	<form name="newchapter" method="post" class="col s12" action="/wp-content/themes/book-writer/write/php/new-chapter.php">
		<input type="hidden" name="book-id" value="<?php echo $book->ID;?>">
		<ul class="collapsible">
			<li class="active">
				<div class="collapsible-header">Chapter</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
							<textarea name="chapter_title" class="materialize-textarea"></textarea>
							<label for="chapter_title">Chapter Title*</label>
						</div>
					</div>
					<div class="row">
						<div class="input-field col s12">
							<textarea name="chapter_content" id="chapter_content_new-<?php echo $book->ID; ?>" class="materialize-textarea"></textarea>
							<label for="chapter_content">Chapter*</label>
							<span class="helper-text" onclick="document.getElementById('<?php echo 'chapter_content_new-' . $book->ID; ?>').requestFullscreen()">Write Distraction Free</span>
						</div>
					</div>
				</div>
			</li>
			<li>
				<div class="collapsible-header">Author Notes</div>
				<div class="collapsible-body">
					<div class="row">
						<div class="input-field col s12">
							<textarea name="pre_chapter" class="materialize-textarea"></textarea>
							<label for="pre_chapter">Pre-Chapter Notes</label>
						</div>
					</div>
					<div class="row">
						<div class="input-field col s12">
							<textarea name="post_chapter" class="materialize-textarea"></textarea>
							<label for="post_chapter">Pre-Chapter Notes</label>
						</div>
					</div>
				</div>
			</li>
		</ul>
		<button class="waves-effect waves-light btn-small right" type="submit">Create</button>
	</form>
<?php }