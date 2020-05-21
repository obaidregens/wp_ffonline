<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');
$dir = explode('wp-content',__FILE__)[0] . 'sitemap';
if (! file_exists($dir)){
    mkdir($dir);
}

function f_stamp($time){
    return gmdate(DATE_W3C,$time);
}
function f_dt($time){
    return str_replace(' ','T',$time) . '+00:00';
}
function dss($string){
    $string_arr = explode('://',$string);
    return $string_arr[0] . '://' . str_replace('//','/',$string_arr[1]);
}
function url_field($loc,$lastmod,$changefreq){
    $construct = '<url>';
    $construct .= '<loc>' . $loc . '</loc>';
    $construct .= '<lastmod>' . $lastmod . '</lastmod>';
    $construct .= '<changefreq>' . $changefreq . '</changefreq>';    
    $construct .= '</url>';
    return $construct;
}
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$book_query = new WP_Query( array(
    'post_type'              => array( 'book' ),
    'post_status'            => array( 'publish' ),
    'order'                  => 'DESC',
    'orderby'                => 'modified',
    'posts_per_page'		 => 10,
) );
//Main Page
$xml .= url_field(
    'https://fanfiction.online/',
    f_dt($book_query->posts[0]->post_modified),
    'hourly'
);
//Pages
$num_pages = $book_query->max_num_pages;
for ($i=2; $i <= $num_pages; $i++) { 
    $xml .= url_field(
        'https://fanfiction.online/?page=' . $i . '/',
        f_dt($book_query->posts[0]->post_modified),
        'hourly'
    );
}

//Collection Page
$terms = (new WP_Term_Query(array(
    'count'         => 1,
    'fields'        => 'ids',
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
    'order'         => 'DESC'
    
)))->terms;
$xml .= url_field(
    'https://fanfiction.online/collection/',
    f_stamp(get_term_meta($terms[0],'time_modified',true)),
    'daily'
);
$xml .= '</urlset>';
file_put_contents ($dir . '/sitemap-general.xml',$xml);

$book_query = new WP_Query( array(
    'post_type'              => array( 'book' ),
    'post_status'            => array( 'publish' ),
    'order'                  => 'DESC',
    'orderby'                => 'modified',
    'posts_per_page'		 => 100,
) );
$num_pages = $book_query->max_num_pages;
for ($i=1; $i <= $num_pages; $i++) {
    $file = $dir . '/sitemap-book-' . $i . '.xml';
    if (! file_exists($file) || filemtime($file) < time() - 172800){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';        
        $books = (new WP_Query(array(
            'post_type'              => array( 'book' ),
            'post_status'            => array( 'publish' ),
            'order'                  => 'DESC',
            'orderby'                => 'modified',
            'posts_per_page'		 => 100,
            'paged'                  => $i,
        )))->posts;
        foreach($books as $book){
            $xml .= url_field(
                dss(get_permalink($book->ID) . '/'),
                f_dt($book->post_modified),
                'monthly'
            );
            //Chapters
            $chapters = published_chapters($book->ID);
            foreach($chapters as $chapter){
                $xml .= url_field(
                    dss (get_permalink($chapter->ID) . '/'),
                    f_dt($chapter->post_modified),
                    'weekly'
                );
            }
        }
        $xml .= '</urlset>';
        file_put_contents ($file,$xml);
    }
}
$num_pages = intval(wp_count_terms('collection',array(
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
))/100)+1;
for ($i=1; $i <= $num_pages; $i++) {
    $file = $dir . '/sitemap-collection-' . $i . '.xml';
    if (! file_exists($file) || filemtime($file) < time() - 172800){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $terms = (new WP_Term_Query(array(
            'count'         => 100,
            'offset'        => ($i * 100) - 100,
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
            'order'         => 'DESC'
        )))->terms;

        foreach($terms as $term){
            $xml .= url_field(
                dss (get_term_link($term->term_id) . '/') ,
                f_stamp(get_term_meta($term->term_id,'time_modified',true)),
                'weekly'
            );
        }
        $xml .= '</urlset>';
        file_put_contents ($file,$xml);
    }
}

//Users
$users = new WP_User_Query( array(
    'number'    => 1,
    'paged'     => 1
) );
$num_pages = intval($users->get_total()/100)+1;
for ($i=1; $i <= $num_pages; $i++) {
    $file = $dir . '/sitemap-author-' . $i . '.xml';
    if (! file_exists($file) || filemtime($file) < time() - 172800){
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $users = (new WP_User_Query( array(
            'number'    => 100,
            'paged'     => $i
        ) ))->get_results();
        foreach($users as $user_obj){
            $user = $user_obj->data;
            $author_books = (new WP_Query( array(
                'author__in'             => array($user->ID),
                'post_type'              => array( 'book' ),
                'post_status'            => array( 'publish' ),
                'order'                  => 'DESC',
                'orderby'                => 'modified',
                'posts_per_page'		 => 1,
            ) ))->posts;
            if (empty($author_books)){
                continue;
            }
            $author_time = f_dt($author_books[0]->post_modified);
            $xml .= url_field(
                dss (get_author_posts_url($user->ID) . '/'),
                $author_time,
                'monthly'
            );
            $xml .= url_field(
                dss(get_author_posts_url($user->ID) . '/books/'),
                $author_time,
                'monthly'
            );
            $xml .= url_field(
                dss(get_author_posts_url($user->ID) . '/updates/'),
                $author_time,
                'monthly'
            );
            $xml .= url_field(
                dss(get_author_posts_url($user->ID) . '/collection/'),
                $author_time,
                'monthly'
            );
        }
        $xml .= '</urlset>';
        file_put_contents ($file,$xml);
    }
}

//Sitemap Index
$index_xml = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$all_files = scandir($dir);
foreach ($all_files as $file_) {
    if (strpos($file_,'sitemap') !== false && $file_ != 'sitemap-index.xml'){
        $index_xml .= '<sitemap><loc>https://fanfiction.online/sitemap/' . $file_ . '</loc></sitemap>';
    }
}
$index_xml .= '</sitemapindex>';
file_put_contents ($dir . '/sitemap-index.xml',$index_xml);