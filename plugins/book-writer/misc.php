<?php
function username_possible($username){
	if (strlen($username) < 5 || strlen($username) > 20){
		return false;
	}
	if (strlen(preg_replace ('/(\d)|(\.)|(_)|([A-Z])+/i','',$username)) > 0){
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
function create_args_from_url(){
	if (empty($_GET) == false){
		$taxonomies = ['tag','category','rating','language','status','genre','character','pairing'];
		if (isset($_GET['words'])){
    		$_GET['words'] = explode(',',$_GET['words']);
    		$meta_query = array(
    			'relation' => 'AND',
    			array(
    				'key'     => 'word-count',
    				'value'   => $_GET['words'][0],
    				'compare' => '>=',
    				'type'    => 'NUMERIC',
    			),
    			array(
    				'key'     => 'word-count',
    				'value'   => $_GET['words'][1],
    				'compare' => '<=',
    				'type'    => 'NUMERIC',
    			),
    		);
		}
		$tax_query = array(
			'relation' => 'AND',
		);
		foreach ($taxonomies as $taxonomy){
			if ($taxonomy == 'category'){
				$_GET['category_included'] = $_GET['fandom_included'];
				$_GET['category_excluded'] = $_GET['fandom_excluded'];
			}
			$included = array();
			if (isset($_GET[$taxonomy . '_included'])){
				$included = explode(',',$_GET[$taxonomy . '_included']);
			}
			$excluded = array();
			if (isset($_GET[$taxonomy . '_excluded'])){
				$excluded = explode(',',$_GET[$taxonomy . '_excluded']);
			}
			if (empty($included) == false){
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'terms'            => $included,
					'field'            => 'term_id',
					'operator'         => 'AND',
				);
			}
			if (empty($excluded) == false){
				$tax_query[] = array(
					'taxonomy'         => $taxonomy,
					'terms'            => $excluded,
					'field'            => 'term_id',
					'operator'         => 'NOT IN',
				);
			}
		}
		if (isset($_GET['only'])){
			if ($_GET['only'] == 'only'){
				$cats = get_terms( array(
					'taxonomy' => 'category',
					'childless' => true,
				) );
				$excluded_cats = array_diff(array_column($cats, 'term_id'),$_GET['category']);

				$tax_query[] = array(
					'taxonomy'         => 'category',
					'terms'            => $excluded_cats,
					'field'            => 'term_id',
					'operator'         => 'NOT IN',		
				);			
			}
		}
		$args = array(
			'post_type'              => array( 'book' ),
			'post_status'            => array( 'publish' ),
			's'                      => str_replace(" ","+",$_GET['search']),
			'posts_per_page'		 => 10,
			'post__not_in'	 => get_stats_of('user_hidden',get_current_user_id()),
		);
		if (isset($_GET['words'])){
			$args['meta_query'] = $meta_query;
		}
		if (isset($_GET['page'])){
			$args['paged'] = intval($_GET['page']);
		}
		$args['tax_query'] = $tax_query;

		$sort_options = ['modified/DESC','date/DESC','favorites/DESC','words/DESC'];
		$sort = explode('/',$_GET['sort']);
	}
	else{
		$args = array(
			'post_type'      		 => array( 'book' ),
			'post_status'            => array( 'publish' ),
			'posts_per_page' 		 => 10,
			'order'                  => 'DESC',
			'orderby'                => 'modified',
			'post__not_in'	 		 => get_stats_of('user_hidden',get_current_user_id()),
		);	
	}
	return $args;
}
function add_to_block_list($user_id){
	if (! is_in_block_list($user_id) && $user_id != get_current_user_id()){
		if (! metadata_exists('user',get_current_user_id(),'block_list')){
			update_user_meta(get_current_user_id(),'block_list',array($user_id));
			return;
		}
		$block_list = get_user_meta(get_current_user_id(),'block_list',true);
		$block_list[] = $user_id;
		update_user_meta(get_current_user_id(),'block_list',$block_list);		
	}
}
function delete_from_block_list($user_id){
	if (is_in_block_list($user_id)){
		$block_list = get_user_meta(get_current_user_id(),'block_list',true);
		unset($block_list[array_search($user_id,$block_list)]);
		$block_list = array_values($block_list);
		update_user_meta(get_current_user_id(),'block_list',$block_list);		
	}
}
function is_in_block_list($user_id,$of_user = null){
	if ($of_user == null){
		$of_user = get_current_user_id();
	}
	if (! metadata_exists('user',$of_user,'block_list')){
		return false;
	}
	$block_list = get_user_meta($of_user,'block_list',true);
	if (in_array($user_id,$block_list)){
		return true;
	}
	else{
		return false;
	}
}
function get_all_unread_messages(){
	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy'         => 'message_between',
			'terms'            => array(strval(get_current_user_id())),
			'field'            => 'name',
			'operator'         => 'AND',
			'include_children' => false,
		),
		array(
			'taxonomy'         => 'message_status',
			'terms'            => array('Sent'),
			'field'            => 'name',
			'operator'         => 'AND',
			'include_children' => false,
		),
	);
	// WP_Query arguments
	$args = array(
		'post_type'              => array( 'message' ),
		'post_status'            => array( 'publish' ),
		'tax_query'              => $tax_query,
		'posts_per_page'		 => -1,
		'order'                  => 'ASC',
		'author__not_in'		 => array(get_current_user_id()),
		'orderby'                => 'date',
	);

	// The Query
	$query = new WP_Query( $args );
	$unread = $query->found_posts;	
	return $unread;
}
function get_unread_messages_with($user_id){
    $user = get_user_by('id',$user_id);
    if(get_current_user_id() == $user->ID){
        $unread = 0;
    }
    else{
        $terms = array(strval(get_current_user_id()),strval($user->ID));
        $tax_query = array(
        	'relation' => 'AND',
        	array(
        		'taxonomy'         => 'message_between',
        		'terms'            => $terms,
        		'field'            => 'name',
        		'operator'         => 'AND',
        		'include_children' => false,
        	),
        	array(
        		'taxonomy'         => 'message_status',
        		'terms'            => array('Sent'),
        		'field'            => 'name',
        		'operator'         => 'AND',
        		'include_children' => false,
        	),
        );
		$cats = get_terms( array(
			'taxonomy' => 'message_between',
		) );
		$excluded_cats = array_diff(array_column($cats, 'name'),$terms);
		$tax_query[] = array(
			'taxonomy'			=> 'message_between',
			'terms'				=> $excluded_cats,
			'field'				=> 'name',
			'operator'			=> 'NOT IN',		
		);
        // WP_Query arguments
        $args = array(
        	'post_type'              => array( 'message' ),
        	'post_status'            => array( 'publish' ),
        	'tax_query'              => $tax_query,
        	'order'                  => 'ASC',
        	'author'                 => $user->ID,
        	'orderby'                => 'date',
			'posts_per_page'		 => -1,
        );
        
        // The Query
        $query = new WP_Query( $args );
        $unread = $query->found_posts;
    }
	return $unread;
}
function find_current_user_chats(){
	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy'         => 'message_between',
			'terms'            => array(strval(get_current_user_id())),
			'field'            => 'name',
			'operator'         => 'AND',
			'include_children' => false,
		),
	);
	// WP_Query arguments
	$args = array(
		'post_type'              => array( 'message' ),
		'post_status'            => array( 'publish' ),
		'tax_query'              => $tax_query,
		'order'                  => 'DESC',
		'orderby'                => 'date',
		'posts_per_page'		 => -1,
	);

	// The Query
	$query = new WP_Query( $args );
	$messages = $query->posts;
	$users = array();
	foreach($messages as $message){
		$terms = array_column(get_the_terms($message->ID,'message_between'),'name');
		if (count($terms) == 1 && $terms[0] == get_current_user_id()){}
		else{
			$key = array_search(get_current_user_id(), $terms);
			unset($terms[$key]);
			$terms = array_values($terms);
		}
		if (! in_array($terms[0],$users)){
			$users[] = $terms[0];
		}
	}
	return $users;
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
function record_visit(){
	if(! is_admin() && is_user_logged_in()){
		update_user_meta(get_current_user_id(),'last_visit',current_time('timestamp',true));
	}
}
add_action('wp_loaded','record_visit');
function login_only(){
	if ($_GET['logged_out'] == true){
    	wp_logout();
    	?><script>location.reload();</script><?php
	}
	if(! is_user_logged_in()){
		get_header();
		get_template_part( 'template-parts/content', 'login' );
		get_sidebar();
		get_footer();
		exit();
	}
}
function working_page($coming_soon = false,$part = false,$link = null,$time = null){
	if ($link == null){
		$link = '/author/ffonline';
	}
	if (! current_user_can('administrator')){
		if ($part == false){
			get_header();
		}
		//$time = "Jan 5, 2021 15:37 UTC";
		?>
<div class="">
	<div class="row section">
		<?php if ($coming_soon == false) { ?>
			<div class="col s12 center-align">Sorry, this page is under maintainence right now, but we're working to get it active as soon as we can, you can see more on why you're seeing this <a href="<?php echo $link; ?>">here</a>.</div>
		<?php } else { ?>
			<div class="col s12 center-align">Coming Soon!, more details <a href="<?php echo $link; ?>">here</a>.</div>
		<?php } ?>
	</div>
	<?php
	if ($time != null){
		echo '<p style="display:none;" id="get">' . $time . '</p>';
	?>
	<div class="row section">
		<div class="col s12">
			<div class="countdown-cont">
				<h1 id="head">Time until is available:</h1>
				<ul>
					<li><span id="days"></span>days</li>
					<li><span id="hours"></span>Hours</li>
					<li><span id="minutes"></span>Minutes</li>
					<li><span id="seconds"></span>Seconds</li>
				</ul>
			</div>

			<style>
				* {
					box-sizing: border-box;
					margin: 0;
					padding: 0;
				}


				.countdown-cont {
					color: #333;
					text-align: center;
					margin: 0 auto;
				}

				h1 {
					font-weight: normal;
				}

				li {
					display: inline-block;
					font-size: 1.5em;
					list-style-type: none;
					padding: 1em;
					text-transform: uppercase;
				}

				li span {
					display: block;
					font-size: 4.5rem;
				}
			</style>
			<script>
				const second = 1000,
					  minute = second * 60,
					  hour = minute * 60,
					  day = hour * 24;

				let countDown = new Date(document.getElementById('get').innerHTML).getTime(),
					x = setInterval(function() {

						let now = new Date().getTime(),
							distance = countDown - now;

						document.getElementById('days').innerText = Math.floor(distance / (day)),
							document.getElementById('hours').innerText = Math.floor((distance % (day)) / (hour)),
							document.getElementById('minutes').innerText = Math.floor((distance % (hour)) / (minute)),
							document.getElementById('seconds').innerText = Math.floor((distance % (minute)) / second);
							  //do something later when date is reached
							  if (distance < 0) {
								clearInterval(x);
								  document.getElementById('days').innerText = 0,
									  document.getElementById('hours').innerText = 0,
									  document.getElementById('minutes').innerText = 0,
									  document.getElementById('seconds').innerText = 0;
							  }

					}, second);
			</script>
		</div>
	</div>
	<?php } ?>
</div>
		<?php
		if ($part == false){
			get_sidebar();
			get_footer();
		}
		exit();
	}
}
//Meta Descriptions, Function called in header
function meta_desc(){
	if (is_home() || is_page('search')) { ?>
		<meta name="Description" content="The best collection of fanfics where readers & writers gather to share their love of fanfiction.">
<?php } else if ( is_singular("chapter")){ global $post; ?>
		<meta name="Description" content="<?php echo get_post($post->post_parent)->post_excerpt; ?>">
<?php } else if ( is_singular("book")){ global $post; ?>
		<meta name="Description" content="<?php echo $post->post_excerpt; ?>">
<?php }
}
//Chapter Title
add_filter( 'pre_get_document_title', function( $title )
	{
		if( is_singular("chapter"))
		{
			global $post;
			return 'Chapter ' . get_post_meta($post->ID,'chapter_order',true) . ' - ' . get_post($post->post_parent)->post_title; 
		}
		if( is_singular("book"))
		{
			global $post;
			return $post->post_title . ' - by ' . get_the_author_meta('display_name',$post->post_author); 
		}
		// Return my custom title
		return $title;
	}
	, 11, 1 );

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

// Function to change email address
function wpb_sender_email( $original_email_address ) {
    return 'support@fanfiction.online';
}
 
// Function to change sender name
function wpb_sender_name( $original_email_from ) {
    return 'Fanfiction Online';
}
 
// Hooking up our functions to WordPress filters 
add_filter( 'wp_mail_from', 'wpb_sender_email' );
add_filter( 'wp_mail_from_name', 'wpb_sender_name' );


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
function hide_admin_bar_from_front_end(){
	if (current_user_can('administrator') == false) {
		return false;
	}
	else{
		return true;
	}
}
add_filter( 'show_admin_bar', 'hide_admin_bar_from_front_end' );

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




function time_format_change($time)
{
	return human_time_diff(get_post_modified_time('U')) . " ago";
}
add_filter( 'get_date', "time_format_change", 100);
add_filter( 'get_the_date', "time_format_change", 100);
add_filter( 'get_the_time', "time_format_change", 100);
add_filter( 'post_date_column_time' , 'time_format_change', 100);
function time_form($status)
{
	return "Updated";
}
add_filter( 'post_date_column_status', 'time_form', 99);

function book_count()
{
	global $post;
	$args = array(
		'post_type' 	=> 'chapter',
		'post_status' 	=> 'publish',
		'post_parent'	=> $post->ID
	);
	$chapters = get_posts($args);
	if (empty($chapters))
	{
		return 0;
	}
	else
	{
		$count = 0;
		foreach ($chapters as $chapter)
		{
			$count += str_word_count($chapter->post_content);
		}
		return $count;
	}
}

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


// add links/menus to the admin bar
function mytheme_admin_bar_render() {
	global $wp_admin_bar;
	$wp_admin_bar->add_menu( array(
		'parent' => 'new-content', // use 'false' for a root menu, or pass the ID of the parent menu
		'id' => 'new_media', // link ID, defaults to a sanitized title value
		'title' => __('Media'), // link title
		'href' => admin_url( 'media-new.php'), // name of file
		'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
	));
	$my_account = $wp_admin_bar->get_node('my-account');
	$newtext = str_replace( 'Howdy,', 'Hi,', $my_account->title );
	$wp_admin_bar->add_node( array(
	'id' => 'my-account',
	'title' => $newtext,
	) );
	if (current_user_can('author')){
		$wp_admin_bar->add_node(array(
		'id' => 'new-content',
		'title' => '<span class="ab-icon"></span><span class="ab-label">'.__( 'Write').'</span>',
		'href' => '',
		'meta' => array(
		'target' => '_self',
		)));
		$wp_admin_bar->add_menu( array(
		'parent' => 'new-content', // use 'false' for a root menu, or pass the ID of the parent menu
		'id' => 'new-book', // link ID, defaults to a sanitized title value
		'title' => __('Book'), // link title
		'href' => admin_url( 'edit.php?post_type=book'), // name of file
			'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
		));
		$wp_admin_bar->add_menu( array(
		'parent' => 'new-content', // use 'false' for a root menu, or pass the ID of the parent menu
		'id' => 'new-chapter', // link ID, defaults to a sanitized title value
		'title' => __('Chapter'), // link title
		'href' => admin_url( 'edit.php?post_type=chapter'), // name of file
			'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
		));
		$wp_admin_bar->add_menu( array(
		'parent' => 'new-content', // use 'false' for a root menu, or pass the ID of the parent menu
		'id' => 'upload-books', // link ID, defaults to a sanitized title value
		'title' => __('Upload Books'), // link title
		'href' => admin_url( 'admin.php?page=upload-books.php'), // name of file
			'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
		));
		
	}
}
//add_action( 'wp_before_admin_bar_render', 'mytheme_admin_bar_render' );