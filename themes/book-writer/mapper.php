<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

function f_stamp($time){
    return gmdate(DATE_W3C,$time);
}
function f_dt($time){
    return str_replace(' ','T',$time) . '+00:00';
}
function url_field($loc,$lastmod,$changefreq){
    $construct = '<url>';
    $construct .= '<loc>' . $loc . '</loc>';
    $construct .= '<lastmod>' . $lastmod . '</lastmod>';
    $construct .= '<changefreq>' . $changefreq . '</changefreq>';    
    $construct .= '</url>';
    return $construct;
}
$book_query = new WP_Query( array(
    'post_type'              => array( 'book' ),
    'post_status'            => array( 'publish' ),
    'order'                  => 'DESC',
    'orderby'                => 'modified',
    'posts_per_page'		 => -1,
) );
$books = $book_query->posts;

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
//Main Page
$xml .= url_field(
    'https://fanfiction.online/',
    f_dt($books[0]->post_modified),
    'hourly'
);
//Pages
$num_pages = intval($book_query->found_posts/10)+1;
for ($i=2; $i <= $num_pages; $i++) { 
    $xml .= url_field(
        'https://fanfiction.online/?page=' . $i,
        f_dt($books[0]->post_modified),
        'hourly'
    );
}
//Books
foreach($books as $book){
    $xml .= url_field(
        get_permalink($book->ID),
        f_dt($book->post_modified),
        'monthly'
    );
    //Chapters
    $chapters = published_chapters($book->ID);
    foreach($chapters as $chapter){
        $xml .= url_field(
            get_permalink($chapter->ID),
            f_dt($chapter->post_modified),
            'weekly'
        );
    }
}

$args = array(
    'taxonomy' 		=> 'collection',
    'hide_empty' 	=> true,
	'meta_query' 	=> array(
		'relation' 		=> 'AND',
		array(
			'key'     	=> 'public_collection',
			'value'   	=> 'Public',
			'compare' 	=> '=',
			'type'    	=> 'CHAR',
		),
    ),
    'meta_key'      => 'time_modified',
    'orderby'       => 'meta_value_num',
    'order'         => 'DESC',
);	
$terms = (new WP_Term_Query( $args ))->terms;
//Collection Page
$xml .= url_field(
    'https://fanfiction.online/collection/',
    f_stamp(get_term_meta($terms[0]->term_id,'time_modified',true)),
    'daily'
);
foreach($terms as $term){
    $xml .= url_field(
        get_term_link($term->term_id),
        f_stamp(get_term_meta($term->term_id,'time_modified',true)),
        'weekly'
    );
}
//Users
$users = get_users( array() );
foreach($users as $user_obj){
    $user = $user_obj->data;
    $author_books = (new WP_Query( array(
        'author__in'             => array($user->ID),
        'post_type'              => array( 'book' ),
        'post_status'            => array( 'publish' ),
        'order'                  => 'DESC',
        'orderby'                => 'modified',
        'posts_per_page'		 => -1,
    ) ))->posts;
    if (empty($author_books)){
        $author_time = f_dt($user->user_registered);
    }
    else{
        $author_time = f_dt($author_books[0]->post_modified);
    }
    $xml .= url_field(
        get_author_posts_url($user->ID),
        $author_time,
        'monthly'
    );
    $xml .= url_field(
        str_replace('//','/',get_author_posts_url($user->ID) . '/books'),
        $author_time,
        'monthly'
    );
    $xml .= url_field(
        str_replace('//','/',get_author_posts_url($user->ID) . '/updates'),
        $author_time,
        'monthly'
    );
    $xml .= url_field(
        str_replace('//','/',get_author_posts_url($user->ID) . '/collection'),
        $author_time,
        'monthly'
    );
}
$xml .= '</urlset>';

$dir = explode('wp-content',__FILE__)[0] . 'sitemap';
if (! file_exists($dir)){
    mkdir($dir);
}
file_put_contents ($dir . '/sitemap.xml',$xml);