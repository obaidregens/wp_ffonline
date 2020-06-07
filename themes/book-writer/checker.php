<?php
/**
 * Template Name: Scraper
 *
 *
 */
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
require_once (explode('wp-content',__FILE__)[0] . 'wp-content/themes/book-writer/php/simplehtmldom/simple_html_dom.php');
$fandoms = array(
	array(
		'name' => 'Harry Potter',
		'link' => 'https://www.fanfiction.net/book/Harry-Potter/?&srt=1&r=10',
		'id'   => 50,
	),
	array(
		'name' => 'Twilight',
		'link' => 'https://www.fanfiction.net/book/Twilight/?&srt=1&r=10',
		'id'   => 51,
	),
	array(
		'name' => 'Percy Jackson and the Olympians',
		'link' => 'https://www.fanfiction.net/book/Percy-Jackson-and-the-Olympians/?&srt=1&r=10',
		'id'   => 52,
	),
);
foreach($fandoms as $fandom){
	$dom = file_get_html($fandom['link'], false);
	//collect all user's reviews into an array
	$answer = array();
	$existing = array();
	if(!empty($dom)) {
		$i = 0;
		foreach($dom->find("div.z-list") as $divClass) {
			$current_book = array();
			//title & link
			foreach($divClass->find(".stitle") as $title ) {
				$current_book['title'] = $title->plaintext;
				$link = $title->href;
			}
			foreach($divClass->find("a[href^='/u']") as $author ) {
				$author_name = $author->plaintext;
				$author_link = $author->href;
				$current_book['author_name'] = $author_name;
				$current_book['author_link'] = 'https://www.fanfiction.net' . $author_link;
				break;
			}
			foreach($divClass->find(".z-indent.z-padtop") as $desc_full ) {
				$desc_full = explode("Rated: ",$desc_full->plaintext);
				$current_book['desc'] = $desc_full[0];
				$desc_full[1] = "Rated: " . $desc_full[1];
				$tags_raw = explode(" - ",$desc_full[1]);
				$tags = array();
				if (strpos($desc_full[1],' - Complete')){
					$tags['status'] = 'Complete';
				}
				else{
					$tags['status'] = 'In Progress';
				}
				$tags['rating'] = str_replace('Rated: ','',$tags_raw[0]);
				$tags['language'] = $tags_raw[1];
				if (strpos($tags_raw[2],"Chapter") === false){
					$tags['genre'] = explode("/",$tags_raw[2]);
				}
				else{
					$tags['genre'] = array("General");
				}
				$charpos = 0;
				for ($x = 5; $x <= 10; $x++) {
					if ((strpos($tags_raw[$x],"Reviews") === false && strpos($tags_raw[$x],"Favs") === false && strpos($tags_raw[$x],"Follows") === false && strpos($tags_raw[$x],"Updated") === false && strpos($tags_raw[$x],"Published") === false) && isset($tags_raw[$x])){
						$charpos = $x;

					}
				}
				if ($charpos != 0){
					if (strpos($tags_raw[$charpos],"] ") !== false){
						$char_full = explode("] ",$tags_raw[$charpos]);

						$char_full[0] = str_replace('[','',$char_full[0]);
						$tags['pairing'] = str_replace(", ","/",$char_full[0]);
						$tags_raw[$charpos] = str_replace('[','',$tags_raw[$charpos]);
						$tags_raw[$charpos] = str_replace('] ',', ',$tags_raw[$charpos]);
					}
					$tags['character'] = explode(", ",$tags_raw[$charpos]);
				}
				if (strpos($tags_raw[3],"Chapters:") !== false){
					$num_chapters = str_replace('Chapters: ','',$tags_raw[3]);
				}
				else if (strpos($tags_raw[2],"Chapters:") !== false){
					$num_chapters = str_replace('Chapters: ','',$tags_raw[2]);
				}
				else if (strpos($tags_raw[1],"Chapters:") !== false){
					$num_chapters = str_replace('Chapters: ','',$tags_raw[1]);
				}
				$current_book['link'] = 'https://fanfiction.net' . $link;
				$link = explode('/',str_replace('/s/','',$link));
				$current_book['tags'] = $tags;
				$exists_book = get_posts( array(
					'post_type'		 => 'book',
					'posts_per_page' => 1, // we only want to check if any exists, so don't need to get all of them
					'meta_key' => 'link',
					'meta_value' => $current_book['link'],
					'post_status' => array('publish','draft','trash'),
					'fields' => 'ids', // we don't need it's content, etc.
				) );
				if ( empty( $exists_book ) ) {
					$start = 1;
				}
				else{
					$start = count(get_posts( array(
						'post_type'		 => 'chapter',
						'posts_per_page' => -1,
						'post_status' => array('publish','draft'),
						'post_parent' => $exists_book[0],
						'fields' => 'ids',
					)))+1;
				}
				if ($num_chapters >= $start){
					$chapters = array();
					for ($x = $start; $x <= $num_chapters; $x++) {
						//echo 'https://www.fanfiction.net/s/' . $link[0] . '/' . $x . '/' . $link[2] . '<br>';

						$chapter = file_get_html('https://www.fanfiction.net/s/' . $link[0] . '/' . $x . '/' . $link[2], false);
						$chapters[$x]['content'] = strip_tags($chapter->find("#storytext")[0],'<p>');
						//print_r(explode('. ',$chapter->find("#chap_select option[selected]")[0]->innertext,2)[1]);
						$chapters[$x]['title'] = explode('. ',$chapter->find("#chap_select option[selected]")[0]->innertext,2)[1];
					}
					$current_book['chapters'] = $chapters;
					if ($start == 1){
						$answer[$i] = $current_book;
					}
					else if(empty($current_book['chapters'])){$yoy = 1;}
					else{
						$current_book['ID'] = $exists_book[0];
						$existing[count($existing)] = $current_book;
					}
				}
			}
			$i++;
		}
	}
	foreach ($answer as $book){
		$book_obj = array(
			'post_title' => $book['title'],
			'post_status' => 'publish',
			'post_author' => 37,
			'post_type'   => 'book',
			'post_excerpt' => $book['desc'],
			'comment_status' => 'closed',
		);
		$book_id = wp_insert_post($book_obj);

		$taxonomies = array('rating','language','genre','status');
		foreach($taxonomies as $taxonomy){
			if (isset($book['tags'][$taxonomy])){
				wp_set_object_terms($book_id,$book['tags'][$taxonomy], $taxonomy);
			}
		}
		$p_taxonomies = array('character','pairing');
		foreach($p_taxonomies as $taxonomy){
			if (isset($book['tags'][$taxonomy])){
				foreach($book['tags'][$taxonomy] as $term_name){
					if (term_exists($term_name,$taxonomy) == null){
						wp_insert_term($term_name,$taxonomy,array(
							'parent'      => $fandom['id'],
							)
						);
					}
				}
				wp_set_object_terms($book_id,$book['tags'][$taxonomy], $taxonomy);
			}
		}
		wp_set_object_terms($book_id,$fandom['name'],'category');
		update_post_meta($book_id,'link',$book['link']);
		update_post_meta($book_id,'source_author_name',$book['author_name']);
		update_post_meta($book_id,'source_author_link',$book['author_link']);

		for ($x = 1; $x <= count($book['chapters']); ++$x){
			$chapter = array
			(
				'post_title' 		=> $book['chapters'][$x]['title'],
				'post_content' 		=> $book['chapters'][$x]['content'],
				'post_status' 		=> 'publish',
				'post_author' 		=> 37,
				'comment_status'	=> 'closed',
				'post_type'   		=> 'chapter',
				'post_parent' 		=> $book_id
			);
			$id = wp_insert_post($chapter);
			update_post_meta($id,'chapter_order',$x);
		}
	}
	foreach ($existing as $book){
		update_post_meta($book_id,'source_author_name',$book['author_name']);
		update_post_meta($book_id,'source_author_link',$book['author_link']);
		$start = count(get_posts( array(
			'post_type'		 => 'chapter',
			'posts_per_page' => -1,
			'post_status' => array('publish','draft'),
			'post_parent' => $book['ID'],
		)))+1;
		for ($x = $start; $x <= count($book['chapters']) + $start-1; ++$x)
		{
			$chapter = array
			(
				'post_title' => $book['chapters'][$x]['title'],
				'post_content' => $book['chapters'][$x]['content'],
				'post_status' => 'publish',
				'post_author' => 37,
				'post_type'   => 'chapter',
				'post_parent' => $book['ID'],
			);
			$id = wp_insert_post($chapter);
			update_post_meta($id,'chapter_order',$x);
		}
	}
}
add_front_cache();