<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
//Delete Folder Function
/* 
 * php delete function that deals with directories recursively
 */
function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

        foreach( $files as $file ){
            delete_files( $file );      
        }

        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}
$target_dir = explode('wp-content',__FILE__)[0] . "wp-content/uploads/uploadbooks/";
mkdir($target_dir);


$target_file = $target_dir . basename($_FILES["bookupload"]["name"]);
$uploadOk = 1;
$filetype = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if file already exists
if (file_exists($target_file))
{
	echo "Sorry, file already exists. Please change the file name and upload again.<br>";
	$uploadOk = 0;
}
// Check file size
if ($_FILES["bookupload"]["size"] > 5000000)
{
	echo "Sorry, your file is too large.<br>";
	$uploadOk = 0;
}
// Allow certain file formats
if($filetype != "epub" )
{
	echo "Sorry, only '.epub' files are allowed. Please upload epub files downloaded from 'FF2EBOOK' only!<br>";
	$uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0)
{
	echo "Sorry, your file was not uploaded because of the errors mentioned above.<br>";
	// if everything is ok, try to upload file
}
else
{
	if (move_uploaded_file($_FILES["bookupload"]["tmp_name"], $target_file))
	{

		$zip = new ZipArchive;
		$res = $zip->open($target_file);
		if ($res === TRUE)
		{
			$zip->extractTo($target_dir);
			$zip->close();
			$total_chapters = new FilesystemIterator($target_dir .'OEBPS/Content/', FilesystemIterator::SKIP_DOTS);
			$total_chapters = iterator_count($total_chapters) - 1;
			$summary = file($target_dir .'OEBPS/Content/title.xhtml');
			$book_name = strip_tags($summary[9]);
			$book_desc = preg_replace('/\s+/', ' ',preg_replace( "/\r|\n/", "", str_replace('Summary: ', '', strip_tags($summary[14]))));
			$convertor = trim(strip_tags($summary[25]));
			if ($convertor == 'Converted using www.FF2EBOOK.com')
			{
				echo "Book Name: " . $book_name . "<br>";
				echo "Total Chapters: " . $total_chapters . "<br>";
					$book = array
					(
						'post_title' => $book_name,
						'post_status' => 'draft',
						'post_author' => get_current_user_id(),
						'post_type'   => 'book',
						'post_excerpt' => $book_desc,
					);
					$book_id = wp_insert_post($book);
					update_post_meta($book_id,'simplefavorites_count',0);
				for ($x = 1; $x <= $total_chapters; ++$x)
				{
					$content_full = file($target_dir .'OEBPS/Content/' . $x .'.xhtml');
					$content_lines = count($content_full)-1;
					for ($y = 0; $y <= $content_lines; ++$y)
					{
						if (strpos($content_full[$y],'<div class="chap-title">') != false){
							$title = str_replace($x . '. ', '', strip_tags($content_full[$y+1]));
						}
						else if (strpos($content_full[$y],'<div class="chap-text">') != false){
							$chapter_start_line = $y;
						}
					}
					for ($y = $chapter_start_line; $y <= $content_lines; ++$y)
					{
						$content[$y] = $content_full[$y];
					}
					$content =  preg_replace('/\s+/', ' ',str_replace("</p>", "<br>", str_replace("<p>", "", str_replace(array("<p class='c1'>..</p>",'<p class="c1">'), "<br>", preg_replace( "/\r|\n/", "", strip_tags(implode("",$content),"<p><br>"))))));

					$chapter = array
					(
						'post_title' => $title,
						'post_content' => $content,
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
						'post_type'   => 'chapter',
						'post_parent' => $book_id
					);
					$id = wp_insert_post($chapter);
					update_post_meta($id,'chapter_order',$x);
					unset($content);
					unset($content_full);
				}
				echo "Your book has been succesfully uploaded.";
			}
		}
		else
		{
			echo "This Book couldn't be uploaded!<br>Error Code: " . $res;
		}
	}
	else
	{
		echo "Sorry, there was an error uploading your file.";
	}
}
delete_files($target_dir . "/");

?>