<?php
//Upload Books
function create_upload_books_menu()
{
	add_menu_page('Upload Books','Upload Books','publish_posts','upload-books.php', 'upload_books_menu');
}
add_action( 'admin_menu', 'create_upload_books_menu' );

function upload_books_menu()
{
		?>
			<h1>Upload Books in Epub format.</h1>
			<h2>We wanted to allow writers who migrated to our site the accessibilty of transferring their previous works, without any hassle.</h2>
			<h2>We added this tool so writers could easily upload their works without any copy/pasting whatsoever</h2>
			<strong><h2>NOTE:</h2></strong>
			<h3>1) This only supports Fanfictions downloaded in 'epub' format from <a href="http://ff2ebook.com/">FF2EBOOK.</a> We also wanted to give a shoutout to them for developing such an amazing tool.</h3>
			<h3>2) Please note that any book uploaded will be added as a new book irrespective of whether a book with the same has been uploaded before or not</h3>
			<h4>3) The author for this book will be you.</h4>
			<h4>4) This upload will create a new book under which all of it's chapters will be.</h4>
			<h4>5) This book will immediately be published.</h4>
			<h4>6) After the upload is complete, you will have to add Genre, Pairings and all other tags yourself</h4>
			<h4>Enjoy your time on Fanfiction Online!</h4>

			<br>
			<h2>Upload your file below::</h2>
			<form action="/wp-content/plugins/book-writer/upload-book.php" method="post" enctype="multipart/form-data">
			Select book to upload:
			<input type="file" name="bookupload" id="bookupload">
			<input type="submit" value="Upload Book" name="submit">
			</form>
		<?php
	
}