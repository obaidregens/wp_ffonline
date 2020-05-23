<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

function return_code($code,$extra = 0){
	$_return = array(
		'code'			=>	$code,
		'book_id'		=>	$_POST['book_id']
	);
	if ($extra !== 0){
		$_return['extra'] = $extra;
	}
	echo json_encode($_return);
	exit();
}

//Verify reCAPTCHA
if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
	return_code(8);
}

$_POST = process_data($_POST,'book');
if (! $_POST){
	return_code(9);
}
if ($_POST['publish'] == 'true' && $_POST['book_id'] == 'new'){
	return_code(10);
}
if ($_POST['publish'] == 'true'){
	if (! can_publish($_POST,'book')){
		return_code(11);
	}
	if (! isset($_POST['selected_chapters'])){
		return_code(12);
	}
	foreach($_POST['selected_chapters'] as $chapter_id){
		if (! can_publish_saved_chapter($chapter_id)){
			return_code(13);
		}
	}
	for ($i = 0; $i < count($_POST['selected_chapters']); $i++) { 
		$chapter_id = $_POST['selected_chapters'][$i];
		wp_publish_post( $chapter_id );
		update_post_meta($chapter_id,'chapter_order',$i + 1);
	}
}
if (! isset($_POST['selected_chapters']) || $_POST['publish'] == 'false'){
	$_POST['selected_chapters'] = array();
}
$all_chapters = all_chapters($_POST['book_id'],-1,'ids');
$non_selected = array_diff($all_chapters,$_POST['selected_chapters']);
draft_these_chapters($_POST['book_id'],$non_selected);
// Add the content of the form to $post as an array
$book = array(
	'post_title'    	=> htmlspecialchars($_POST['title']),
	'post_content'  	=> htmlspecialchars($_POST['detailed_description']),
	'post_type'			=> 'book',
	'post_excerpt'		=> htmlspecialchars($_POST['description']),
	'post_status'		=> 'draft'
);
if ($_POST['publish'] == 'true'){
	$book['post_status'] = 'publish';
}

//save the post
if ($_POST['book_id'] == 'new'){
	$book_id = wp_insert_post($book);
}
else{
	$book_id = $_POST['book_id'];
	$book['ID'] = $book_id;
	wp_update_post($book);
}
update_post_meta($book_id,'anon_review',$_POST['anonymous_reviews']);


//Taxonomies
//Simple taxonomies
//"What the hell did I do?"
$taxonomies = array('tag','rating','language','status','genre');
foreach($taxonomies as $taxonomy){
	wp_set_object_terms($book_id,$_POST[$taxonomy], $taxonomy);
}
//Fandom
wp_set_object_terms($book_id,$_POST['fandom'],'category');


//"Let the skyfall"
//"When it crumbles"
//"We will will stand tall"
//wp_set_object_terms() will be called outside loop for performance
$characters_to_set = array();
foreach($_POST['characters'] as $fandom_id => $characters_arr){
	foreach($characters_arr as $character){
		if (term_exists($character,'character',$fandom_id) === null){
			wp_insert_term($character,'character',array(
				'parent'	=> $fandom_id
			));
		}
		$characters_to_set[] = $character;

	}
}
wp_set_object_terms($book_id,$characters_to_set, 'character');

//Pairing
//"Put your hand in my hand and we'll stand..."
$pairings_to_set = [];
foreach($_POST['pairing'] as $pairing){
	sort($pairing);
	$pairings_to_set[] = implode('/',$pairing);
}
wp_set_object_terms($book_id,$pairings_to_set,'pairing');

//"Tell me I've been lied to"

$updated = get_post($book_id);
if ($updated->post_status == 'publish'){
	$response = 1;
	wp_update_post(array(
		'ID'=> $book_id,
		'post_name' => htmlspecialchars($_POST['title']),
	));
}
else if($updated->post_status == 'draft'){
	$response = 2;
	wp_update_post(array(
		'ID'				=> $book_id,
		'post_name'			=> bin2hex(random_bytes(7)),
	));
}
else{
	$response = 7;

}
echo json_encode( array( 'code' => $response, 'book_id' => $book_id));
exit();