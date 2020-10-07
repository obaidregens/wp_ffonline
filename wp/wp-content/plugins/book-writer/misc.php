<?php
function sqlPlaceholder($a,$type = '%s') {
	return implode(',',array_fill(0,count($a),$type));
}
function millitime(){
	return round(microtime(true) * 1000);
}
// Author Link
add_filter( 'author_link', function($link,$author_id,$author_nicename){
	return home_url( '@' ) . get_the_author_meta( 'user_login', $author_id );
}, 10, 3 );

add_filter('flush_rewrite_rules_hard','__return_false');

function mark_search($in, $search, $trim = null) {
	$should_trim = ! ($trim === null || strlen($in) <= $trim);
	$unmarked_trim = $should_trim ? substr($in, 0, $trim) . '...' : $in;
	if ($search === ''){
		return $unmarked_trim;
	}
	$pos = stripos($in, $search);
	if ($pos === false) {
		return $unmarked_trim;
	}
	if ($should_trim) {
		$in = substr($in, max(array($pos - (($trim/2) - strlen($search)) ,0)));
		$in = substr($in, 0, $trim);
		$pos = stripos($in, $search);
	}
	$occurrences = substr_count(strtolower($in),strtolower($search));
	$full = '';
	$trimmed = $in;
	for ($i=0; $i < $occurrences; $i++) {
		$pos = stripos($trimmed, $search);
		$trimmed = substr_replace($trimmed, '<mark>', $pos, 0);
		$trimmed = substr_replace($trimmed, '</mark>', $pos + 6 + strlen($search), 0);
		$full .= substr($trimmed,0,$pos + 6 + strlen($search) + 7);
		$trimmed = substr($trimmed,$pos + 6 + strlen($search) + 7);
	}
	$full .= $trimmed;
	return $should_trim ? $full . '...' : $full;
}
function print_book_tags($book_id,$book_query) {
	$ref = &$book_query->book_tags[$book_id];
	$fandom = $ref['fandom'];
	unset($ref['fandom']);
	$ref = array_merge(['fandom'=>$fandom],$ref);
	?>
	<tags>
	<?php
	foreach ($ref as $taxonomy => $terms){
		?>
		<tag-group name="<?= ucfirst($taxonomy) ?>">
			<?= implode('',array_column($terms,'link')); ?>
		</tag-group>
		<?php
	}
	?>
	</tags>
	<?php
}
function print_book_meta($book_id) {
	$collections = collection_books::query_by('book_id',$book_id);
	$collections = collection::query([
		'id_included'	=> array_column($collections,'collection_id'),
		'types'			=> ['Favorites','Public'],
	]);
	?>
	<book-meta>
		<span tooltip-top="Updated"><?= get_the_time('',$book_id); ?></span>
		<span tooltip-top="Words"><?= get_post_meta($book_id,'word-count',true); ?></span>
		<span tooltip-top="Collections"><?= count($collections); ?></span>
		<span tooltip-top="Votes"><?= count(vote::query_by('story','type_id',$book_id)); ?></span>
	</book-meta>
	<?php
}
function a_intersect($arrayOne, $arrayTwo){
    $index = array_flip($arrayOne);
    $second = array_flip($arrayTwo);

    $x = array_intersect_key($index, $second);

    return array_flip($x);
}
function timer($logtext,$echo = false){
    global $lastlogtime;
    $now = microtime(true);
    $diff = $now - ($lastlogtime ?? $now);
	file_put_contents(MAIN_DIR . '/content/time_logger.txt', $logtext . ": " . $diff . "\r\n", FILE_APPEND );
	if ($echo === true){
		echo $logtext . ": " . $diff . "<br>";
	}
    $lastlogtime = microtime(true);
}
function author_href($_post_id){
	$book = get_post($_post_id);
	$_author = intval($book->post_author);
	$a_href = "";
	if ($_author === 37){
		// Author Name
		$old_name = get_post_meta( $book->ID,'source_author_name',true );
		$new_name = get_post_meta( $book->ID,'author_name',true );
		$tname = $new_name !== '' ? $new_name : $old_name;

		$new_link = get_post_meta( $book->ID,'ffn_author_id',true );
		if ($new_link !== ''){
			return "<a href=\"/ffn@$new_link\">$tname</a>";
		}
		$old_link = get_post_meta( $book->ID,'source_author_link',true );
		return "<a rel=\"nofollow\" href=\"$old_link\">$tname</a>";
	}
	$a_href .= '<a href="' . get_author_posts_url($_author) . '">' . get_the_author_meta( 'display_name', $_author ) . '</a>';
	return $a_href;
}
function author_name_single($_post_id){
	$book = get_post($_post_id);
    $_author = intval($book->post_author);

	$ffonline_name = get_the_author_meta( 'display_name',$_author );
	if ($_author === 37){
		$old_name = get_post_meta( $book->ID,'source_author_name',true );
		$new_name = get_post_meta( $book->ID,'author_name',true );
		return $new_name !== '' ? $new_name : ($old_name !== '' ? $old_name : $ffonline_name);
	}
	return $ffonline_name;
}

function username_regex_valid($string){
	if (strlen(preg_replace ('/(\d)|(\.)|(_)|([A-Z])+/i','',$string)) > 0){
		return false;
	}
	return true;
}
function title_regex_valid($string){
	if (strlen(preg_replace ('/(\d)|(\.)|( )|(\-)|(\?)|(\_)|([A-Z])+/i','',$string)) > 0){
		return false;
	}
	return true;
}
//SMTP SETUp

add_action( 'phpmailer_init', 'wpse8170_phpmailer_init' );
function wpse8170_phpmailer_init( PHPMailer $phpmailer ) {
    $phpmailer->Mailer     = 'smtp';
    $phpmailer->Host       = defined('SMTP_HOST') ? SMTP_HOST : '';
    $phpmailer->SMTPAuth   = defined('SMTP_AUTH') ? SMTP_AUTH : true;
    $phpmailer->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $phpmailer->Username   = defined('SMTP_USER') ? SMTP_USER : '';
    $phpmailer->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
    $phpmailer->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
    $phpmailer->From       = defined('SMTP_FROM') ? SMTP_FROM : '';
    $phpmailer->FromName   = defined('SMTP_NAME') ? SMTP_NAME : '';
    
    $phpmailer->IsSMTP();
}


add_action( 'wp_print_styles', 'wps_deregister_styles', 100 );
function wps_deregister_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}
function remove_dns_prefetch( $hints, $relation_type ) {
    if ( 'dns-prefetch' === $relation_type ) {
        return array();
    }

    return $hints;
}

add_filter( 'wp_resource_hints', 'remove_dns_prefetch', 10, 2 );

// Remove default wp packages
remove_action( 'wp_default_scripts', 'wp_default_scripts' );
remove_action( 'wp_default_scripts', 'wp_default_packages' );

// REMOVE WP EMOJI

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

remove_action( 'embed_head', 'print_emoji_detection_script' );

remove_action( 'wp_before_admin_bar_render', 'wp_customize_support_script' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rel_canonical' );

function disable_json_api () {

  // Filters for WP-API version 1.x
  add_filter( 'json_enabled', '__return_false' );
  add_filter( 'json_jsonp_enabled', '__return_false' );

  // Filters for WP-API version 2.x
  add_filter( 'rest_enabled', '__return_false' );
  add_filter( 'rest_jsonp_enabled', '__return_false' );

}
add_action( 'after_setup_theme', 'disable_json_api' );
// Disable REST API link tag
remove_action('wp_head', 'rest_output_link_wp_head', 10);

// Disable oEmbed Discovery Links
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);

// Disable REST API link in HTTP headers
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

//Remove Feeds
remove_action( 'wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
remove_action( 'wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
remove_action( 'wp_head', 'index_rel_link' ); // index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 ); // start link
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version
function fb_disable_feed() {
	global $wp_query;
    $wp_query->set_404();
    status_header(404);
	exit();
}

add_action('do_feed', 'fb_disable_feed', 1);
add_action('do_feed_rdf', 'fb_disable_feed', 1);
add_action('do_feed_rss', 'fb_disable_feed', 1);
add_action('do_feed_rss2', 'fb_disable_feed', 1);
add_action('do_feed_atom', 'fb_disable_feed', 1);
add_action('do_feed_rss2_comments', 'fb_disable_feed', 1);
add_action('do_feed_atom_comments', 'fb_disable_feed', 1);


add_filter( 'script_loader_tag', 'add_asyn_to_script', 10, 3 );

function add_asyn_to_script( $tag, $handle, $src ) {
	if (is_admin()){
		return $tag;
	}
    //you can use this to make all async
    $tag = str_replace( ' src', ' defer src', $tag );

    // You can use this to make it work as below for a specific script
//    if ( 'dropbox.js' === $handle ) 
  //      $tag = '<script async type="text/javascript" src="' . esc_url( $src ) . '"></script>';
    //}

    return $tag;
}

remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' );

function username_possible($username){
	if (strlen($username) < 5 || strlen($username) > 20){
		return false;
	}
	if (! username_regex_valid($username)){
		return false;
	}
	if (username_exists($username)){
		return false;
	}
	return true;
}
function verify_reCAPTCHA($response){
    $postdata = http_build_query(
        array(
            'secret' => '6Lc_ROEUAAAAADrnccSNHPP3whrLFAM5qrPAK4-S',
            'response' => $response
        )
    );
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context  = stream_context_create($opts);
    $result = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context),true);
	return $result;
}
function logged_in_status($user,$chat = false){
	if (get_user_meta($user->ID,'online_status',true) != 'hide' && get_user_meta(get_current_user_id(),'online_status',true) != 'hide'){
		if(is_int(get_user_meta($user->ID,'last_visit',true)) && (current_time('timestamp',true) - get_user_meta($user->ID,'last_visit',true)) < 15){
			echo '<i class="fas fa-circle right online"></i>';
		}
		else{
			echo '<i class="fas fa-circle right offline"></i>';
		}		
	}
	if ((get_user_meta($user->ID,'read_receipts',true) == 'hide' || get_user_meta(get_current_user_id(),'read_receipts',true) == 'hide') && $chat == true){
		echo '<span class="right"><em>Read receipts not available.</em></span>';
	}
}
//Meta Descriptions, Function called in header
function meta_desc(){
	if (is_home() || is_page('search') || is_page('read')) { ?>
		<meta name="Description" content="The best collection of fanfics where readers & writers gather to share their love of fanfiction.">
<?php } else if ( is_singular("chapter")){ global $post; ?>
		<meta name="Description" content="<?php echo get_post($post->post_parent)->post_excerpt; ?>">
<?php } else if ( is_singular("book")){ global $post; ?>
		<meta name="Description" content="<?php echo $post->post_excerpt; ?>">
<?php }
}
add_action('wp_head','meta_desc',1);
////User IP column
function add_users_ip_column($column) {
	$column['user_ip'] = 'IP Address';
	return $column;
}
add_filter('manage_users_columns','add_users_ip_column');
function display_users_ip($val,$column,$user_id) {
	$user = get_userdata($user_id);
	switch ($column) { 
		case 'user_ip' : return $user->user_ip; break;
		default: }
	return $return;
}
add_filter('manage_users_custom_column','display_users_ip',10,3);

//disable search (query vars)
function myplugin_register_query_vars( $vars ) {
	if (is_admin()){
		return $vars;
	}
	$to_remove = array('search','rating','language','character','pairing','status','category_name','page','paged','s');
	foreach($vars as $key => $var){
		if (in_array($var,$to_remove)){
			unset($vars[$key]);
		}
	}
    return $vars;
}
add_filter( 'query_vars', 'myplugin_register_query_vars' );

function default_comments_off( $data ) {
    if( $data['post_type'] == 'page' && $data['post_status'] == 'auto-draft' ) {
        $data['comment_status'] = 0;
    }

    return $data;
}
add_filter( 'wp_insert_post_data', 'default_comments_off' );

function wpd_comment_notification_text( $notify_message, $comment_id ){
    // get the current comment and post data
    $comment = get_comment( $comment_id );
    $post = get_post( $comment->comment_post_ID );
    // don't modify trackbacks or pingbacks
    if( '' == $comment->comment_type ){
        // build the new message text
        $notify_message  = sprintf( __( 'New comment on your book "%s"' ), $post->post_title ) . "\r\n";
        $notify_message .= sprintf( __('Author : %1$s'), $comment->comment_author ) . "\r\n";
        $notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
        $notify_message .= __('You can see comments for this book here: ') . "\r\n";
        $notify_message .= get_permalink($comment->comment_post_ID) . "#comments\r\n\r\n";

    }
    // return the notification text
    return $notify_message;
}
add_filter( 'comment_notification_text', 'wpd_comment_notification_text', 20, 2 );

function wpautop_not($s)
{
    //remove any new lines already in there
    $s = str_replace("\n", "", $s);

    //remove all <p>
    $s = str_replace("<p>", "", $s);

    //replace <br /> with \n
    $s = str_replace(array("<br />", "<br>", "<br/>"), "\n", $s);

    //replace </p> with \n\n
    $s = str_replace("</p>", "\n\n", $s);       

    return $s;      
}

function ace_block_wp_admin() {
	if ( is_admin() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		wp_safe_redirect( home_url() . '/404' );
		exit;
	}
}
add_action( 'admin_init', 'ace_block_wp_admin' );
function add_last_nav_item($items) {
	$items .= '<li><a href="/collection">Collections</a></li>';
	if (is_user_logged_in()){
		return $items .= "<li><a href='/dashboard'>Dashboard</a></li>";
	}
	else{
		return $items .= '<li><a href="/dashboard" >Login</a></li>';
	}
}
add_filter('wp_nav_menu_header_items','add_last_nav_item');


add_action('admin_head', 'hide_menu_author');
function hide_menu_author()
{
	if (current_user_can('author'))
	{
		$GLOBALS['menu'] = array();
		?>
		<style>#adminmenuback,#adminmenuwrap{display:none !important}
		#wpcontent, #footer{margin-left:0 !important}</style>
		<?php
	}
}
show_admin_bar( false );


function get_chapters_query()
{
	global $post;
	if ($post->post_type == 'book'){
		$post_parent = $post -> ID;
	}
	elseif ($post->post_type == 'chapter'){
		$post_parent = $post->post_parent;
	}
	// Chapters Query arguments
	$args = array
	(
		'post_parent'           => $post_parent,
		'post_type'             => array( 'chapter' ),
		'post_status'           => array( 'publish' ),
		'meta_key'				=> 'chapter_order',
		'orderby'				=> 'meta_value_num',
		'order'					=> 'ASC',
		'posts_per_page'		=> -1,
	);
	// The Query
	$chapters = new WP_Query( $args );
	return $chapters;
}




function time_format_change($time,$format,$post)
{
	return human_time_diff(get_post_modified_time('U',false,$post));
}
add_filter( 'get_date', "time_format_change", 100, 3);
add_filter( 'get_the_date', "time_format_change", 100, 3);
add_filter( 'get_the_time', "time_format_change", 100, 3);
add_filter( 'post_date_column_time' , 'time_format_change', 100, 3);
function time_form($status)
{
	return "Updated";
}
add_filter( 'post_date_column_status', 'time_form', 99);



function dont_show_cheatin_page() {
	do_action( 'template_redirect' );
 	global $wp_query;
  	$wp_query->set_404();
  	status_header( 404 );
  	get_template_part( 404 );
	
	exit();
} 
add_action('admin_page_access_denied', 'dont_show_cheatin_page', 30);
add_filter('adminimize_nopage_access_message', 'dont_show_cheatin_page', 30);

function hide_ip_from_non_admins ($comment_author_IP)
{
	if ( current_user_can( 'manage_options' ) == false)
	{
		$comment_author_IP = false;
	}
    return $comment_author_IP;
}
add_filter( 'get_comment_author_IP', 'hide_ip_from_non_admins');

function hide_email_from_non_admins ($comment_author_email)
{
	if ( current_user_can( 'manage_options' ) == false)
	{
		$comment_author_email = false;
	}
    return $comment_author_email;
}
add_filter( 'comment_email', 'hide_email_from_non_admins');

function hide_link_from_non_admins ($comment_author_link)
{
	if ( current_user_can( 'manage_options' ) == false)
	{
		$comment_author_link = false;
	}
    return $comment_author_link;
}
add_filter( 'get_comment_author_url', 'hide_link_from_non_admins');




add_action( 'init', 'exclude_page_post_from_search', 99 );

function exclude_page_post_from_search() {
	global $wp_post_types;

	if ( post_type_exists( 'page' ) ) {

		// exclude from search results
		$wp_post_types['page']->exclude_from_search = true;
	}
	if ( post_type_exists( 'post' ) ) {

		// exclude from search results
		$wp_post_types['post']->exclude_from_search = true;
	}
}



function posts_for_current_author($query) {
    global $pagenow;
 
    if( 'edit.php' != $pagenow || !$query->is_admin )
        return $query;
 
    if( !current_user_can( 'edit_others_posts' ) ) {
        global $user_ID;
        $query->set('author', $user_ID );
    }
    return $query;
}
add_filter('pre_get_posts', 'posts_for_current_author');

