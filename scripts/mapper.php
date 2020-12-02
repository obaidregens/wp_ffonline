<?php
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
define("NO_ROUTES",true);
require ($maindir . '/content/index.php');

$dir = $maindir . '/sitemap';
if (! file_exists($dir)){
    mkdir($dir);
}

function f_stamp($time){
    return gmdate(DATE_W3C,$time);
}
function f_dt($time){
    return str_replace(' ','T',$time) . '+00:00';
}
function u($url){
    return "https://fanfiction.online/" . trim($url,'/') . "/";
}
function url_field($loc,$lastmod,$changefreq){
    $construct = '<url>';
    $construct .= '<loc>' . $loc . '</loc>';
    $construct .= '<lastmod>' . $lastmod . '</lastmod>';
    $construct .= '<changefreq>' . $changefreq . '</changefreq>';    
    $construct .= '</url>';
    return $construct;
}

// Settings
$site = "https://fanfiction.online";
$cache_time = 60*60*24*2; // 48 hours

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$book_query = new book_query( [] );
$last_updated = f_dt($book_query->books[0]->post_modified);
// Main Page
$xml .= url_field(
    u("/read"),
    $last_updated,
    'hourly'
);
// Pages
$num_pages = $book_query->pages;
for ($i=2; $i <= $num_pages; $i++) { 
    $xml .= url_field(
        u("/read?page=$i"),
        $last_updated,
        'hourly'
    );
}
$book_query = null;

// Collection Page
$collections_all = collection::query([
    'orderby'   => 'created',
    'order'     => 'DESC',
    'types'     => ['Public'],
    'count'     => [
        'from'      => 1,
    ]
]);
if (!empty($collections_all)) {
    $xml .= url_field(
        u("collections/"),
        f_stamp($collections_all[0]->created),
        'daily'
    );    
}
$xml .= '</urlset>';
file_put_contents ($dir . '/sitemap-general.xml',$xml);

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Fandoms
$fandoms = array_column($wpdb->get_results("SELECT `_value` FROM search_cache WHERE _key = 'fandom'"),'_value');
foreach ( $fandoms as $fandom_id ) {
    $xml .= url_field(
        u("/read?fandom_included=$fandom_id"),
        $last_updated,
        'hourly'
    );
}
$fandoms = null;
$xml .= '</urlset>';
file_put_contents($dir . '/sitemap-fandoms.xml',$xml);

$book_query = new book_query( array(
    'per_page'		 => 100,
) );
$num_pages = $book_query->pages;
$book_query = null;
for ($i=1; $i <= $num_pages; $i++) {
    $file = $dir . '/sitemap-story-' . $i . '.xml';
    if (file_exists($file) && (time() - filemtime($file)) < $cache_time ) {
        continue;
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $books = (new book_query([
        'per_page'      => 100,
        'page'          => $i,
    ]))->books;
    foreach ($books as $book) {
        $xml .= url_field(
            u("story/".$book->ID),
            f_dt($book->post_modified),
            'weekly'
        );
        //Chapters
        $chapters = published_chapters($book->ID,-1,'ids');
        foreach($chapters as $k => $chapter){
            $xml .= url_field(
                u("story/".$book->ID.'/'.($k+1)),
                f_dt($book->post_modified),
                'weekly'
            );
        }
    }
    $xml .= '</urlset>';
    file_put_contents ($file,$xml);
}
$books = null;

$total_num = count($collections_all);
$num_pages = intval(($total_num/100)+1);
if ($total_num % 100 === 0){
    $num_pages = intval($total_num/100);
}
for ($i=1; $i <= $num_pages; $i++) {
    $file = $dir . '/sitemap-collection-' . $i . '.xml';
    if (file_exists($file) && (time() - filemtime($file)) < $cache_time ) {
        continue;
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $collections = array_slice($collections_all,($i*100)-100,100);
    foreach($collections as $collection){
        $xml .= url_field(
            rtrim(collection_helpers::link($collection), '/') . '/' ,
            f_stamp($collection->created),
            'weekly'
        );
    }
    $xml .= '</urlset>';
    file_put_contents ($file,$xml);
}
$collections = null;
$collections_all = null;

// Users
$users = new WP_User_Query( array(
    'number'    => 1,
    'paged'     => 1
) );
$total_num = $users->get_total();
$num_pages = intval(($total_num/100)+1);
if ($total_num % 100 === 0){
    $num_pages = intval($total_num/100);
}
for ($i=1; $i <= $num_pages; $i++) {
    $file = $dir . '/sitemap-author-' . $i . '.xml';
    if (file_exists($file) && (time() - filemtime($file)) < $cache_time ) {
        continue;
    }
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    $users = (new WP_User_Query( [
        'number'    => 100,
        'paged'     => $i
    ] ))->get_results();
    foreach($users as $user_obj){
        $user = $user_obj->data;
        $author_books = (new book_query([
            'included'		=> [
                'author'		=> [$user->ID]
            ],
            'per_page'		=> 1
        ]))->books;
        if (empty($author_books)){
            continue;
        }
        $author_time = f_dt($author_books[0]->post_modified);
        $xml .= url_field(
            u('@'.$user->user_login),
            $author_time,
            'weekly'
        );
        $xml .= url_field(
            u('@'.$user->user_login . '/stories'),
            $author_time,
            'weekly'
        );
        $xml .= url_field(
            u('@'.$user->user_login . '/updates'),
            $author_time,
            'weekly'
        );
        $xml .= url_field(
            u('@'.$user->user_login . '/collections'),
            $author_time,
            'weekly'
        );
    }
    $xml .= '</urlset>';
    file_put_contents ($file,$xml);
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