<?php
function construct_page_title(... $parts) {
    return 'Fanfiction Online - ' . implode(" - ",$parts);
}
require_once(__DIR__ . '/helpers.php');
define('WP_USE_THEMES', false);
require(__DIR__ . '/wp/wp-load.php');
class Router {
    public $request;
    private $r;
    private $called = [];
    function __construct(){
        $this->request = explode('?',strtolower($_SERVER['REQUEST_URI']))[0];
        $this->request = $this->request === '' ? '/' : $this->request;
        $this->r = arr::non_empty(explode('/',$this->request));
        $this->request = '/' . implode('/',$this->r);
    }
    function listen($dyno_url,$func){
        $match = arr::non_empty(explode('/',$dyno_url));
        if ( empty($match) && !empty($this->r) ){
            return;
        }
        $params = [];
        foreach ($match as $i => $m) {
            if ($m === '&*'){
            break;
            }
            $portion = &$this->r[$i];
            if (! isset($portion)){
                return;
            }
            if ($m === '*'){
            break;
            }
            if ( isset($this->r[$i+1]) && ! isset($match[$i+1]) ){
                return;
            }
            $m_colon_split = explode(':',$m,2);
            $m_portion = substr($portion,strlen($m_colon_split[0]));
            if (
                count($m_colon_split) > 1 &&
                $m_colon_split[1] !== '' &&
                substr($portion,0,strlen($m_colon_split[0])) === $m_colon_split[0] &&
                $m_portion !== ''
            ){
                $params[$m_colon_split[1]] = $m_portion;
                continue;
            }
            if ($m === $portion){
                continue;
            }
            return;
        }
        $this->params = $params;
        $func($this);
    }
    function template ($template,$once = false){
        $template = '/' . ltrim($template,'/');
        $tp = __DIR__ . $template . '.php';
        if (! file_exists($tp)){
            echo 'Template not Found: ' . $template;
            return;
        }

        if ($once){
            $app = $this;
            require_once $tp;
            return;
        }
        $app = $this;
        require $tp;
    }
    function header($options = []){
        if (in_array('header',$this->called)){
            return;
        }
        $this->called[] = 'header';
        $this->header_options = array_replace([
            'title'         => 'Fanfiction Online',
            'description'   => 'The best collection of fanfics where readers & writers gather to share their love of fanfiction.'
        ],$options);
        $this->template('/views/header',true);
    }
    function footer(){
        if (in_array('footer',$this->called)){
            return;
        }
        $this->called[] = 'footer';
        $this->template('/views/footer',true);
    }
    function _404($template = null){
        $this->type = '404';
        $this->type_id = 0;
        http_response_code(404);
        $this->header();
        $this->template($template === null ? 'views/404' : $template);
        $this->footer();
        exit();
    }
    function _301($url){
        header("Location: " . $url, true, 301);
        exit();
    }
    function login(){
        if (is_user_logged_in(  )){
            return;
        }
        $this->header();
        $this->footer();
        ?>
        <script>
        window.onload = function(){
            prompt_login();
            document.querySelector('popup-overlay').style.pointerEvents = 'none';
        }
        </script>
        <?php
        exit();
    }
}
global $app;
$app = new Router();
$app->listen('/',function($self){
    $self->type = 'home';
    $self->type_id = 0;
    $self->header();
    $self->template('/views/search');
    $self->footer();
    exit();
});
$app->listen('/api',function($self){
    include('scripts/api.php');
    exit();
});
$app->listen("/book/:book/", function($self){
    $query = (new WP_Query(array(
        'post_type'         => array('book'),
        'post_name__in'     => array($self->params['book'])
    )))->posts;
    if (! empty($query)){
        $self->_301('/book/' . $query[0]->ID);
    }
});
$app->listen('/book/:book/',function($self){
    $book = get_post($self->params['book']);
    if ($book === null || $book->post_type !== 'book' || $book->post_status !== 'publish' ){
        $self->_404();
    }
    $self->type = 'book';
    $self->type_id = intval($book->ID);
    $self->book = $book;
    $self->header([
        'title'         => construct_page_title($book->post_title . ' by ' . author_name_single($book->ID)),
        'description'   => $book->post_excerpt
    ]);
    $self->template('/views/book');
    $self->footer();
    exit();
});
$app->listen("/book/:book/chapter/:chapter", function($self){
    $bquery = (new WP_Query(array(
        'post_type'         => array('book'),
        'post_name__in'     => array($self->params['book'])
    )))->posts;
    if (empty($bquery)){
        return;
    }
    $cquery = (new WP_Query(array(
        'post_type' => array('chapter'),
        'meta_query' => array(
            array(
                'key'       => 'chapter_order',
                'value'     => $self->params['chapter'],
                'type'      => 'NUMERIC',
            ),
        ),
        'post_parent__in'   => array($bquery[0]->ID)
    )))->posts;
    if (! empty($cquery)){
        $self->_301('/book/' . $bquery[0]->ID . '/' . $self->params['chapter'] );
    }
});
$app->listen('/book/:book/:chapter',function($self){
    $book = get_post($self->params['book']);
    if ($book === null || $book->post_type !== 'book' || $book->post_status !== 'publish' ){
        $self->_404();
    }
    $query = (new WP_Query(array(
        'post_type' => array('chapter'),
        'meta_query' => array(
            array(
                'key'       => 'chapter_order',
                'value'     => $self->params['chapter'],
                'type'      => 'NUMERIC',
            ),
        ),
        'post_parent__in'   => array($book->ID)
    )))->posts;
    if (empty($query)){
        $self->_404();
    }
    $chapter = $query[0];
    if ($chapter === null || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish'){
        $self->_404();
    }
    $self->type = 'chapter';
    $self->type_id = intval($chapter->ID);
    $self->book = $book;
    $self->chapter = $chapter;
    $self->header([
        'title'         => construct_page_title(
            'Chapter ' . get_post_meta($chapter->ID,'chapter_order',true),
            $book->post_title . ' by ' . author_name_single($book->ID)
        ),
        'description'   => $book->post_excerpt
    ]);
    $self->template('/views/chapter');
    $self->footer();
    exit();
});
// Collections
$app->listen('/collections',function($self){
    $self->type = 'collection-index';
    $self->type_id = 0;
    $self->header();
    $self->template('/views/collections/index');
    $self->footer();
    exit();
});
$app->listen('/collections/:collection',function($self){
    $collection_query = collection::query(array(
        'slug'  => $self->params['collection'],
        'types' => array('Public','Unlisted')
    ));
    if (empty($collection_query)){
        return;
    }
    $self->type = 'collection';
    $self->type_id = intval($collection_query[0]['ID']);
    $self->collection = $collection_query[0];
    $self->header();
    $self->template('/views/collections/single');
    $self->footer();
    exit();
});
$app->listen('/@:user/collections/:collection',function($self){
    $user = get_user_by( 'login', $self->params['user'] );
    if ($user === false){
        return;
    }
    $types = ['Favorites'];
    if (intval($user->ID) === intval(get_current_user_id())){
        $types[] = 'Private';
    }
    $collection_query = collection::query(array(
        'slug'      => $self->params['collection'],
        'authors'   => array($user->ID),
        'types'     => $types
    ));
    if (empty($collection_query)){
        return;
    }
    $self->type = 'collection';
    $self->type_id = intval($collection_query[0]['ID']);
    $self->collection = $collection_query[0];
    $self->header();
    $self->template('/views/collections/single');
    $self->footer();
    exit();
});
// Author
function author_template_load($template){
    global $app;
    $user = get_user_by( 'login', $app->params['user'] );
    if ($template === 'settings' && intval($user->ID) !== intval(get_current_user_id()) ){
        $app->_404();
    }
    if ($user === false){
        $app->_404();
    }
    $app->type = $template === 'about' ? 'author' : 'author-' . $template;
    $app->type_id = intval($user->ID);
    $app->user = $user->data;
    $title = $template === 'about' ?
        construct_page_title('@' . $user->user_login) :
        construct_page_title('@' . $user->user_login,ucfirst($template));
    $app->header([
        'title'         => $title,
        'description'   => ''
    ]);
    $app->template('/views/user/' . $template);
    $app->footer();
    exit();
}
$app->listen('/author/:user',function($self){
    $uquery = (new WP_User_Query(array(
        'nicename'  => $self->params['user']
    )))->results;
    if (! empty($uquery)){
        $self->_301('/@' . $uquery[0]->user_login);
    }
});
$app->listen('/author/:user/:leading',function($self){
    $uquery = (new WP_User_Query(array(
        'nicename'  => $self->params['user']
    )))->results;
    if (! empty($uquery)){
        $self->_301('/@' . $uquery[0]->user_login . '/' . $self->params['leading']);
    }
});
$app->listen('/@:user/books',function($self){
    author_template_load('books');
});
$app->listen('/@:user/updates',function($self){
    author_template_load('updates');
});
$app->listen('/@:user/collections',function($self){
    author_template_load('collections');
});
$app->listen('/@:user/settings',function($self){
    author_template_load('settings');
});
$app->listen('/@:user',function($self){
    author_template_load('about');
});
function ffn_author_template_load($template){
    global $app;
    $self_user = c_user::get($app->params['ffn_author']);
    if ($self_user !== false){
        $user = get_user_by( 'ID', $self_user );
        $app->_301('/@' . $user->user_login);
    }
    $author_books = new book_query([
        'included'      => [
            'ffn_author'    => [$app->params['ffn_author']]
        ]
    ]);
    if (empty($author_books->books)){
        $app->_404();
    }
    $app->type = $template === 'about' ? 'ffn_author' : 'ffn_author-' . $template;
    $app->type_id = intval($app->params['ffn_author']);
    $app->author_id = intval($app->params['ffn_author']);
    $app->author_books = $author_books;
    $app->author_name = get_post_meta( $author_books->books[0]->ID, 'author_name', true );
    $title = $template === 'about' ?
        construct_page_title($app->author_name) :
        construct_page_title($app->author_name,ucfirst($template));
    $app->header([
        'title'         => $title,
        'description'   => ''
    ]);
    $app->template('/views/ffn_user/' . $template);
    $app->footer();
    exit();
}
$app->listen('/ffn@:ffn_author',function($self){
    ffn_author_template_load('about');
});
$app->listen('/ffn@:ffn_author/books',function($self){
    ffn_author_template_load('books');
});
// Inbox
$app->listen('/inbox',function($self){
    $self->type = 'inbox';
    $self->type_id = 0;
    $self->login();
    $self->header();
    $self->template('/views/inbox');
    $self->footer();
    exit();
});
$app->listen('/inbox/@:username',function($self){
    $user = get_user_by( 'login', $self->params['username'] );
    if ($user === false || intval($user->ID) === intval(get_current_user_id()) ){
        $self->_404();
    }
    $self->type = 'inbox';
    $self->type_id = intval($user->ID);
    $self->login();
    $self->header();
    $self->template('/views/inbox');
    $self->footer();
    exit();
});
// Write
$app->listen('/write',function($self){
    $self->type = 'write';
    $self->type_id = 0;
    $self->login();
    $self->header();
    echo 'Coming Soon!';
    $self->footer();
    exit();
});
// Verify Account
$app->listen('/verify',function($self){
    $self->type = 'verify';
    $self->type_id = 0;
    $self->header();
    $self->template('/views/verify');
    $self->footer();
    exit();
});
// Verify Account
$app->listen('/contact',function($self){
    $self->type = 'contact';
    $self->type_id = 0;
    $self->header();
    $self->template('/views/contact');
    $self->footer();
    exit();
});
// Robots
$app->listen('/robots.txt',function(){
?>
<pre style="word-wrap: break-word; white-space: pre-wrap;">
User-agent: *
Allow: /
Disallow: /write/
Disallow: /inbox/

Sitemap: https://fanfiction.online/sitemap/sitemap-index.xml
</pre>
<?php
exit();
});
// 404
$app->listen('&*',function($self){
    $self->_404();
});