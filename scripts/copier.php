<?php
$start = microtime(true);

define('WP_USE_THEMES', false);
$wp_dir = rtrim(explode('content',__DIR__,2)[0],'/\\') . '/';
require( $wp_dir . 'wp-load.php');

function term_replace($taxonomy, $term, $parent){
    $exists = term_exists($term,$taxonomy,$parent);
    if ($exists !== null){
        return $exists['term_id'];
    }
    return wp_insert_term($term,$taxonomy,array(
        'parent'      => $parent,
        )
    );
}
if (! defined('ARCHIVER_PATH')){
    echo 'ARCHIVER_PATH not defined';
    exit();
}
$temp_dir = $wp_dir . trim(ARCHIVER_PATH,'/') . '/temp/';
if (! file_exists($temp_dir)){
    mkdir($temp_dir);
}
$files = scandir($temp_dir);
foreach ($files as $f_key => $file) {
    // Last Condition to give buffer InCase File is still being updated.
    if ($file === '.' || $file === '..' || time() - intval(explode('-',$file)[0]) < 900 ){
        unset($files[$f_key]);
    }
}
sort($files);
if ( count($files) < 1 ){
    echo 'No files';
    exit();
}
foreach ($files as $file ) {
    $books = file($temp_dir . $file);
    $fandoms = array();
    foreach ($books as $book_line => $book ) {
        if ((microtime(true) - $start) > 100){
            echo 'Timeout';
            exit();
        }
        $book = json_decode($book,true);

        // Fandom
        foreach ($book['Tags']['Fandom'] as $fandom_name) {
            if (! isset($fandoms[$fandom_name])){
                $books_term = get_term_by( 'name', 'Books', 'category' );
                if ($books_term === false){
                    echo 'No Books Term';
                    exit();
                }
                $fandoms[$fandom_name] = term_replace($fandom_name,'category',$books_term->term_id);
            }    
        }
        $book_obj = array(
			'post_title'        => $book['Title'],
			'post_status'       => 'publish',
			'post_author'       => 37,
			'post_type'         => 'book',
			'post_excerpt'      => $book['Description'],
			'comment_status'    => 'closed',
        );
        if ($book['Exists'] === false){
            $book_id = wp_insert_post( $book_obj );
        }
        else{
            $book_id = $book['Exists'];
            $book_obj['ID'] = $book_id;
            wp_update_post( $book_obj );
        }
        $taxonomies = array(
            'Rated'     => 'rating',
            'Language'  => 'language',
            'Genre'     => 'genre',
            'Status'    => 'status',
            'Fandom'    => 'category'
        );
		foreach($taxonomies as $key => $taxonomy){
			if (isset($book['Tags'][$key])){
				wp_set_object_terms($book_id,$book['Tags'][$key], $taxonomy);
			}
        }
        // Characters
        if (isset($book['Tags']['Relationships'])){
            foreach ($book['Tags']['Relationships'] as $key => $value) {
                sort($value);
                $book['Tags']['Relationships'][$key] = implode('/',$value);
            }    
        }

        $p_taxonomies = array(
            'All Characters'      => 'character',
            'Relationships'       => 'pairing'
        );
		foreach($p_taxonomies as $key => $taxonomy){
            if (! isset($book['Tags'][$key])){
                continue;
            }
            wp_set_object_terms($book_id,$book['Tags'][$key], $taxonomy);
        }
        update_post_meta($book_id,'ffn_book_id',$book['_id']);
        update_post_meta($book_id,'ffn_author_id',$book['Author ID']);
        update_post_meta($book_id,'author_name',$book['Author Name']);
        $book['Exists'] = $book_id;
        $existing_chapters = count(all_chapters($book_id,-1,'ids'));
        foreach ($book['Chapters'] as $key => $chapter) {
            $x = $key+1 + $existing_chapters;
            $chapter = array(
				'post_title' 		=> $chapter['title'],
				'post_content' 		=> $chapter['content'],
				'post_status' 		=> 'publish',
				'post_author' 		=> 37,
				'comment_status' 	=> 'closed',
				'post_type'   		=> 'chapter',
				'post_parent' 		=> $book_id,
			);
			$chapter_id = wp_insert_post($chapter);
            update_post_meta($chapter_id,'chapter_order',$x);

            // Chapters Key
            unset($book['Chapters'][$key]);
            $books[$book_line] = json_encode($book) . "\n";
            file_put_contents($temp_dir . $file,implode('',$books));
            if ((microtime(true) - $start) > 100){
                echo 'Timeout';
                exit();
            }
        }
        unset($books[$book_line]);
        file_put_contents($temp_dir . $file,implode('',$books));
    }
    unlink($temp_dir . $file);
}
echo 'Completed';