<?php
function construct_page_title(... $parts) {
    return implode(" - ",$parts) . " - Fanfiction Online";
}
define('MAIN_DIR',dirname(__DIR__) . '/');
require_once(__DIR__ . '/php_includes/helpers.php');

define('WP_USE_THEMES', false);
require(__DIR__ . '/wp/wp-load.php');

require_once(__DIR__ . '/php_includes/mail/mail.php');
require_once(__DIR__ . '/php_includes/GeoIP/geoip.php');

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
    function static($file) {
        $loc = MAIN_DIR . ltrim($file,'/');
        $ext = pathinfo($loc)['extension'];
        $mime = json_decode(file_get_contents(__DIR__ . '/php_includes/mime-type.json'),true)['.' . $ext];
        header("Content-Type: $mime");
        readfile($loc);
        exit();
    }
    function header($options = []){
        if (in_array('header',$this->called)){
            return;
        }
        $this->called[] = 'header';
        $this->header_options = array_replace([
            'title'         => 'Fanfiction Online'
        ],$options);
        $this->header_options['title'] = htmlspecialchars($this->header_options['title']);
        if (isset($this->header_options['description'])) {
            $this->header_options['description'] = htmlspecialchars($this->header_options['description']);
        }
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
        $this->header([
            'title'         => construct_page_title("Page not found"),
        ]);
        $this->template($template === null ? 'views/404' : $template);
        $this->footer();
        exit();
    }
    function _301($url){
        header("Location: " . $url, true, 301);
        exit();
    }
    function redirect($url){
        header("Location: " . $url);
        exit();
    }
    function login(){
        if (is_user_logged_in(  )){
            return;
        }
        $this->type = $this->type ?? 'login';
        $this->type_id = $this->type_id ?? 0;
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
    function admin() {
        if (current_user_can( 'administrator' )){
            return;
        }
        $this->_404();
        exit();
    }
}
global $app;
$app = new Router();
$app->listen('/',function($self){
    $self->type = 'home';
    $self->type_id = 0;
    $self->header([
        'title'         => "Fanfiction Online - Read & Write Fanfiction",
        'description'   => "Discover & read the most popular fanfiction stories in your fandom, with the best app to read and write fanfiction!"
    ]);
    $self->template('/views/home');
    $self->footer();
    exit();
});
$app->listen('/logout',function($self){
    if (! is_user_logged_in()) {
        $self->redirect('/login');
    }
    $self->type = 'logout';
    $self->type_id = 0;
    wp_logout();
    $self->redirect('/read');
});
$app->listen('/news',function($self){
    $self->type = 'news';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("News")
    ]);
    $self->template('/views/news');
    $self->footer();
    exit();
});
$app->listen('/read',function($self){
    $self->type = 'read';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Read")
    ]);
    $self->template('/views/search');
    $self->footer();
    exit();
});
$app->listen('/api',function($self){
    include('scripts/api.php');
    exit();
});
$app->listen('/dash',function($self){
    $self->admin();
    $self->type = 'dash-home';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Dash")
    ]);
    $self->template('/views/dash/home');
    $self->footer();
    exit();
});
$app->listen('/dash/faq',function($self){
    $self->admin();
    $self->type = 'dash-faq';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Dash FAQ")
    ]);
    $self->template('/views/dash/faq');
    $self->footer();
    exit();
});
$app->listen('/dash/tags',function($self){
    $self->admin();
    $self->type = 'dash-tags';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Dash Tags")
    ]);
    $self->template('/views/dash/tags');
    $self->footer();
    exit();
});
$app->listen('/dash/contact',function($self){
    $self->admin();
    $self->type = 'dash-contact';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Dash Contact")
    ]);
    $self->template('/views/dash/contact');
    $self->footer();
    exit();
});
$app->listen('/manage',function($self){
    $self->admin();
    $self->type = 'manage';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Manage")
    ]);
    $self->template('/views/manage');
    $self->footer();
    exit();
});
$app->listen("/book/:story/", function($self){
    $query = (new WP_Query(array(
        'post_type'         => array('book'),
        'post_name__in'     => array($self->params['story'])
    )))->posts;
    if (! empty($query)){
        $self->_301('/story/' . $query[0]->ID);
    }
});
$app->listen('/story/:story/',function($self){
    $story = get_post($self->params['story']);
    if ($story === null || $story->post_type !== 'book' || $story->post_status !== 'publish' ){
        $self->_404();
    }
    $self->type = 'story';
    $self->type_id = intval($story->ID);
    $self->story = $story;
    $self->header([
        'title'         => construct_page_title($story->post_title . ' by ' . author_name_single($story->ID)),
        'description'   => $story->post_excerpt
    ]);
    $self->template('/views/book');
    $self->footer();
    exit();
});
$app->listen("/book/:story/chapter/:chapter", function($self){
    $bquery = (new WP_Query(array(
        'post_type'         => array('book'),
        'post_name__in'     => array($self->params['story'])
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
        $self->_301('/story/' . $bquery[0]->ID . '/' . $self->params['chapter'] );
    }
});
$app->listen('/story/:story/:chapter',function($self){
    $story = get_post($self->params['story']);
    if ($story === null || $story->post_type !== 'book' || $story->post_status !== 'publish' ){
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
        'post_parent__in'   => array($story->ID)
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
    $self->story = $story;
    $self->chapter = $chapter;
    $self->header([
        'title'         => construct_page_title(
            'Chapter ' . get_post_meta($chapter->ID,'chapter_order',true),
            $story->post_title . ' by ' . author_name_single($story->ID)
        ),
        'description'   => $story->post_excerpt
    ]);
    $self->template('/views/chapter');
    $self->footer();
    exit();
});
// Collections
$app->listen('/collections',function($self){
    $self->type = 'collection-index';
    $self->type_id = 0;
    $self->header([
        'title'     => construct_page_title("Collections")
    ]);
    $self->template('/views/collections/index');
    $self->footer();
    exit();
});
$app->listen('/collections/:collection',function($self){
    $collection = collection::get_by('slug',$self->params['collection']);
    if ($collection && $collection->type === "Unlisted") {}
    else {
        $collection = collection::get_by('ID',$self->params['collection']);
        if ( !$collection || $collection->type !== 'Public' ) {
            return;
        }
    }
    $self->type = 'collection';
    $self->type_id = intval($collection->ID);
    $self->collection = $collection;
    $followed = count(collection_follow::query_by('type_id',$collection->ID));
    $and_is_followed = "";
    if ($followed > 5) {
        $and_is_followed = " and is followed by " . $followed  . "people";
    }
    $self->header([
        'title'         => construct_page_title($collection->title,"Collection"),
        'description'   => $collection->title . ' has ' . $collection->count . ($collection->count > 1 ? ' stories' : ' story') . $and_is_followed . '.'
    ]);
    $self->template('/views/collections/single');
    $self->footer();
    exit();
});
$app->listen('/@ffonline/&*',function($self) {
    if (! current_user_can( 'administrator' )){
        $self->_404();
    }
});
$app->listen('/@me/&*',function($self) {
    if (!is_user_logged_in()) {
        return;
    }
    $u = get_userdata( get_current_user_id() );
    $self->redirect("/@" . $u->user_login . substr($self->request,4));
});
$app->listen('/@:user/collections/:collection',function($self){
    $user = (get_user_by( 'login', $self->params['user'] ))->data;
    if ($user === false){
        return;
    }
    $str_proper = ucfirst(strtolower($self->params['collection']));
    $special_titles = ['Favorites','Hidden'];
    $args = [ 'author_included' => [$user->ID] ];
    if (in_array($str_proper,$special_titles)) {
        $args['title'] = $str_proper;
    }
    else {
        $args['id_included'] = [$self->params['collection']];
        $args['types'] = ['Private'];
    }
    $collection = collection::query($args);
    if (empty($collection)) {return;}
    $collection = $collection[0];

    $self->type = 'collection';
    $self->type_id = intval($collection->ID);
    $self->collection = $collection;
    $self->header([
        'title'         => $defined_title ?? construct_page_title($collection->title,"Collection")
    ]);
    $self->template('/views/collections/single');
    $self->footer();
    exit();
});
// Author
function author_template_load($template){
    global $app;
    $user = get_user_by( 'login', $app->params['user'] );
    if ( $template === 'settings' && ! is_current_user($user->ID) ){
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
$app->listen('/@:user/stories',function($self){
    author_template_load('stories');
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
$app->listen('/ffn@:ffn_author/stories',function($self){
    ffn_author_template_load('stories');
});
// Inbox
$app->listen('/inbox',function($self){
    $self->type = 'inbox';
    $self->type_id = 0;
    $self->login();
    $self->header([
        'title'         => construct_page_title("Inbox"),
    ]);
    $self->template('/views/inbox');
    $self->footer();
    exit();
});
$app->listen('/inbox/@:username',function($self){
    $self->login();
    $user = get_user_by( 'login', $self->params['username'] );
    if ($user === false || intval($user->ID) === intval(get_current_user_id()) ){
        $self->_404();
    }
    $self->type = 'inbox';
    $self->type_id = intval($user->ID);
    $self->header([
        'title'         => construct_page_title("Inbox"),
    ]);
    $self->template('/views/inbox');
    $self->footer();
    exit();
});
// Import Stories
$app->listen('/import-stories',function($self){
    $self->login();
    $self->type = 'import-stories';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Import Stories"),
    ]);
    $self->template('/views/import_stories');
    $self->footer();
    exit();
});
$app->listen('/login',function($self) {
    $self->login();
    $self->redirect('/my-stories');
});
// Write
$app->listen('/my-stories',function($self){
    $self->login();
    $self->type = 'my-stories';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("My Stories"),
    ]);
    $self->template('/views/books/my-books');
    $self->footer();
    exit();
});
$app->listen('/my-stories/:id',function($self){
    $self->login();
    $story = get_post( $self->params['id'] );
    if (
        $self->params['id'] !== 'new' &&
        (
            ! $story
            || ! is_current_user($story->post_author)
        )
    ) {
        return;
    }
    $self->type = 'edit-story';
    $self->type_id = $self->params['id'] === 'new' ? 'new' : intval($story->ID);
    $self->story = $self->params['id'] === 'new' ? 'new' : $story;
    $self->header([
        'title'         => construct_page_title("Edit Story"),
    ]);
    $self->template('/views/books/edit');
    $self->footer();
    exit();
});
$app->listen('/create-fandom',function($self){
    $self->login();
    $self->type = 'create-fandom';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Create Fandom"),
    ]);
    $self->template('/views/create-fandom');
    $self->footer();
    exit();
});
$app->listen('/write',function($self){
    $self->_301('/my-stories');
});
$app->listen('/drafts',function($self){
    $self->login();
    $self->type = 'drafts-index';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Drafts"),
    ]);
    $self->template('/views/drafts/index');
    $self->footer();
    exit();
});
$app->listen('/drafts/:draft_share',function($self){
    if ($self->params['draft_share'] === 'new') {
        return;
    }
    $self->login();
    $draft = drafts::get_by('share',$self->params['draft_share']);
    if ($draft === false) {
        return;
    }
    $self->type = 'drafts-share';
    $self->type_id = intval($draft->ID);
    $self->draft = $draft;
    $self->header([
        'title'         => construct_page_title($draft->title,"Shared by @" . get_userdata($draft->user_id )->user_login,"Drafts"),
    ]);
    $self->template('/views/drafts/preview');
    $self->footer();
    exit();
});
$app->listen('/drafts/:draft_id',function($self){
    $self->redirect( '/drafts/' . $self->params['draft_id'] . '/edit' );
});
$app->listen('/drafts/:draft_id/preview',function($self){
    $self->login();
    $draft = drafts::get_by('ID',$self->params['draft_id']);
    if ($draft === false || intval($draft->user_id) !== intval(get_current_user_id()) ) {
        return;
    }
    $self->type = 'drafts-preview';
    $self->type_id = intval($draft->ID);
    $self->draft = $draft;
    $self->header([
        'title'         => construct_page_title($draft->title,"Preview","Drafts"),
    ]);
    $self->template('/views/drafts/preview');
    $self->footer();
    exit();
});
$app->listen('/drafts/:draft_id/edit',function($self){
    if (! is_user_logged_in() && $self->params['draft_id'] !== 'new') {
        $self->login();
    }
    $draft = drafts::get_by('ID',$self->params['draft_id']);
    if ($draft === false && $self->params['draft_id'] !== 'new') {
        return;
    }
    if ($draft && !is_current_user($draft->user_id) ) {
        return;
    }
    $self->type = 'drafts-edit';
    $self->type_id = $self->params['draft_id'] === 'new' ? 0 : intval($draft->ID);
    $self->draft = $draft;
    $self->header([
        'title'         => construct_page_title($draft === false ? "Untitled" : $draft->title,"Edit", "Drafts"),
    ]);
    $self->template('/views/drafts/edit');
    $self->footer();
    exit();
});
$app->listen('/drafts/:draft_id/export/ffn/download',function($self){
    $self->login();
    $draft = drafts::get_by('ID', $self->params['draft_id'] );
    if ($draft === false || !is_current_user($draft->user_id) ) {
        return;
    }
    $self->type = 'drafts-download-ffn';
    $self->type_id = intval($draft->ID);
    $file = MAIN_DIR . '/download/draft-' . $draft->ID . '.odt';
    drafts_json::output_odt($draft->content,$file);
    header("Content-Description: File Transfer");
    header("Content-Type: application/vnd.oasis.opendocument.text"); 
    header('Content-Disposition: attachment; filename="' . $draft->title . '.odt"');
    header('Content-Length: ' . filesize($file) );
    header( 'Cache-Control: no-store' );
    readfile($file);
    exit();
});
$app->listen('/drafts/:draft_id/export/ffn',function($self){
    $self->login();
    $draft = drafts::get_by('ID',$self->params['draft_id']);
    if ($draft === false || intval($draft->user_id) !== intval(get_current_user_id()) ) {
        return;
    }
    $self->type = 'drafts-export-ffn';
    $self->type_id = intval($draft->ID);
    $self->draft = $draft;
    $self->header([
        'title'         => construct_page_title($draft->title,"Export to FFN","Drafts"),
    ]);
    $self->template('/views/drafts/export-ffn');
    $self->footer();
    exit();
});
$app->listen('/drafts/:draft_id/export/ao3',function($self){
    $self->login();
    $draft = drafts::get_by('ID',$self->params['draft_id']);
    if ($draft === false || intval($draft->user_id) !== intval(get_current_user_id()) ) {
        return;
    }
    $self->type = 'drafts-export-ao3';
    $self->type_id = intval($draft->ID);
    $self->draft = $draft;
    $self->header([
        'title'         => construct_page_title($draft->title,"Export to AO3","Drafts"),
    ]);
    $self->template('/views/drafts/export-ao3');
    $self->footer();
    exit();
});
// Verify Account
$app->listen('/verify',function($self){
    $self->redirect('/connections');
});
$app->listen('/connections',function($self){
    $self->login();
    $self->type = 'connections';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Connections"),
    ]);
    $self->template('/views/connections/home');
    $self->footer();
    exit();
});
// Contact
$app->listen('/contact',function($self){
    $self->type = 'contact';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Contact"),
    ]);
    $self->template('/views/contact');
    $self->footer();
    exit();
});
// Content Guidlines
$app->listen('/rules',function($self){
    $self->type = 'rules';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Rules"),
    ]);
    $self->template('/views/rules/rules');
    $self->footer();
    exit();
});
// Content Guidlines
$app->listen('/guidelines',function($self){
    $self->type = 'content-guidelines';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Content Guidelines"),
    ]);
    $self->template('/views/rules/content-guidelines');
    $self->footer();
    exit();
});
// Content Guidlines
$app->listen('/privacy',function($self){
    $self->type = 'privacy-policy';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("Privacy Policy"),
    ]);
    $self->template('/views/rules/privacy');
    $self->footer();
    exit();
});
// FAQ
$app->listen('/faq',function($self){
    $self->type = 'faq';
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("FAQ")
    ]);
    $self->template('/views/faq/home');
    $self->footer();
    exit();
});
$app->listen('/faq/:q',function($self){
    $q = questions::get($self->params["q"]);
    if ($q === false || $q->status !== 'public') {
        return;
    }
    $self->faq_question = $q;
    $self->type = 'faq-' . $q->ID;
    $self->type_id = 0;
    $self->header([
        'title'         => construct_page_title("FAQ"),
    ]);
    $self->template('/views/faq/single');
    $self->footer();
    exit();
});
// Manifest
$app->listen('/sw.js',function($self) {
    $self->static('/content/manifest/sw.js');
});
// Offline
$app->listen('/offline',function($self) {
    $self->type = 'offline';
    $self->type_id = 0;
    $self->template('/views/offline');
    exit();
});
// Robots
$app->listen('/robots.txt',function($self){
    $self->static('/content/robots.txt');
});
// Temp Resources
$app->listen('/content/static/:filename',function($self){
    $f = $self->params['filename'];
    if (! in_array($f,['chapters.css','chapters.js','book.css','book.js'])){
        return;
    }
    $a = explode('.',$f);
    $name = $a[0];
    $type = $a[1];
    $b = new bundle("O");
    $filename = $name . '-' . $b->index[$name][$type . '_hash'] . "." . $type;
    $self->redirect('/content/static/bundles/' . $filename);
});
// 404
$app->listen('&*',function($self){
    $self->_404();
});