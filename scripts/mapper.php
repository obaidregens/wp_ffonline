<?php
$maindir = rtrim(explode('content',__DIR__,2)[0],'/\\');
define("NO_ROUTES",true);
require ($maindir . '/content/index.php');

class XML_sitemap {
    protected $fields = [];
    protected $names = [];
    protected $current = "";
    protected function ping($name = null) {
        $name = $name === null ? $this->current : $name;
        $sitemap_file = $this->sitemap_url  . $name . '.xml';
        echo "Pinged Google: " . $sitemap_file;
        file_get_contents("http://www.google.com/ping?sitemap=" . $sitemap_file);
    }
    function __construct($args) {
        $args = array_replace([
            'site'          => '',
            'dir'           => '',
            'sitemap_path'  => 'sitemap',
            'cache_time'    => 60*60*24*2
        ],$args);
        $this->site = rtrim($args['site'],'/') . '/';
        $this->sitemap_url = $this->site . trim($args['sitemap_path'],'/') . '/';
        $this->dir = rtrim($args['dir'],'/') . '/';
        mkdir ( $this->dir , 0777 , true );
        $this->cache_time = $args['cache_time'];
    }
    function add($url,$last_updated,$changefreq) {
        if (!$this->current) {
            return false;
        }
        $url = rtrim($this->site . trim($url,'/'),'/');
        $this->fields[] =
        "<url>".
        "<loc>$url</loc>".
        "<lastmod>$last_updated</lastmod>".
        "<changefreq>$changefreq</changefreq>".
        "</url>";
        return true;
    }
    function open($name) {
        $name = strval($name);
        if ($name !== "") {
            $this->names[] = $name;
        }
        $f_this = $this->dir . $name . ".xml";
        if ( file_exists($f_this) && (time() - filemtime($f_this)) < $this->cache_time ){
            return false;
        }
        if ($this->current) {
            file_put_contents($this->dir . $this->current . ".xml",self::wrapSitemap(implode('',$this->fields)));
            $this->fields = [];
        }
        if (!DEV()) {
            $this->ping();
        }
        $this->current = $name;
        return true;
    }
    function index() {
        $this->open("");
        $index_xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $index_xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($this->names as $xmlName) {
            $index_xml .= '<sitemap><loc>' . $this->sitemap_url  . $xmlName . '.xml</loc></sitemap>';
        }
        $index_xml .= '</sitemapindex>';
        file_put_contents($this->dir . "sitemap-index.xml",$index_xml);
        $index_xml = null;
        // Ping
        if (!DEV()) {
            $this->ping('sitemap-index');
        }        
    }
    protected function wrapSitemap($sitemap) {
        return
        '<?xml version="1.0" encoding="UTF-8"?>'.
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
        $sitemap.
        '</urlset>';
    }
}

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

$x = new XML_sitemap([
    'site'  => 'https://fanfiction.online',
    'dir'           => rtrim(MAIN_DIR,'/') . '/sitemap/',
    'sitemap_path'  => 'sitemap',
    'cache_time'    => 60*60*24*2
]);
$x->open('sitemap-general');

$book_query = new book_query( [] );
$last_updated = f_dt($book_query->books[0]->post_modified);
// Main Page
$x->add("/",$last_updated,'hourly');
$x->add("/read",$last_updated,'hourly');
// Pages
$num_pages = $book_query->pages;
for ($i=2; $i <= $num_pages; $i++) { 
    $x->add('/read?page=$i',$last_updated,'hourly');
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
    $x->add('collections',f_stamp($collections_all[0]->created),'daily');
}

$x->open('sitemap-fandoms');

// Fandoms
$fandoms = array_column($wpdb->get_results("SELECT `_value` FROM search_cache WHERE _key = 'fandom'"),'_value');
foreach ( $fandoms as $fandom_id ) {
    if (!term_exists( intval($fandom_id), 'category' )){
        continue;
    }
    $x->add("read?fandom_included=$fandom_id",$last_updated,'hourly');
}
$fandoms = null;

$book_query = new book_query( array(
    'per_page'		 => 100,
) );
$num_pages = $book_query->pages;
$book_query = null;
for ($i=1; $i <= $num_pages; $i++) {
    if (!$x->open("sitemap-story-$i")) {
        continue;
    }
    $books = (new book_query([
        'per_page'      => 100,
        'page'          => $i,
    ]))->books;
    foreach ($books as $book) {
        $x->add("story/".$book->ID,f_dt($book->post_modified),'weekly');
        //Chapters
        $chapters = published_chapters($book->ID,-1,'ids');
        foreach($chapters as $k => $chapter){
            $x->add('story/'.$book->ID.'/'.($k+1),f_dt($book->post_modified),'weekly');
        }
    }
}
$books = null;

$total_num = count($collections_all);
$num_pages = intval(($total_num/100)+1);
if ($total_num % 100 === 0){
    $num_pages = intval($total_num/100);
}
for ($i=1; $i <= $num_pages; $i++) {
    if (!$x->open("sitemap-collection-$i")) {
        continue;
    }
    $collections = array_slice($collections_all,($i*100)-100,100);
    foreach($collections as $collection){
        $x->add(rtrim(collection_helpers::link($collection), '/') . '/',f_stamp($collection->created),'weekly');
    }
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
    if (!$x->open("sitemap-author-$i")) {
        continue;
    }
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
        $x->add('@'.$user->user_login,$author_time,'weekly');
        $x->add('@'.$user->user_login . '/stories',$author_time,'weekly');
        $x->add('@'.$user->user_login . '/updates',$author_time,'weekly');
        $x->add('@'.$user->user_login . '/collections',$author_time,'weekly');
    }
}

//Sitemap Index
$x->index();