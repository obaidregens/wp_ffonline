<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

function api_add_to_collection(){
    $_POST['data'] = json_decode(str_replace('\\','',$_POST['data']['data_']),true);
    function return_code($code,$extra = 0){
        $_return = array(
            'code'				=>	$code,
        );
        if ($code <= 5){
            $_return['book_collections'] = collection::query_by_book($_POST['data']['book_ids'],'ID');
        }
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    if (! is_user_logged_in()){
        return_code(6);
    }
    $_POST['data']['book_ids'] = array_keys($_POST['data']['book_collections']);
    foreach ($_POST['data']['book_ids'] as $key => $book_id) {
        $return = collection::set('book',$book_id,$_POST['data']['book_collections'][$book_id],null,get_current_user_id());
        if (err::is($return) ){
            return_code(7);
        }
    }
    return_code(1);
}
function api_block_user(){
    $success = false;
    if ($_POST['data']['action'] === 'block'){
        $return = chats::block($_POST['data']['username']);
    }
    else if ($_POST['data']['action'] === 'unblock'){
        $return = chats::unblock($_POST['data']['username']);
    }
    echo json_encode(array(
        'success'   => $return,
    ));
}
function api_bookmark_this(){
    if(! is_user_logged_in()){
        echo 3;
    }
    else if (chapter_bookmark_exists($_POST['data']['chapter'],$_POST['data']['para'])){
        delete_chapter_bookmark($_POST['data']['chapter'],$_POST['data']['para']);
        echo 0;
    }
    else{
        add_chapter_bookmark($_POST['data']['chapter'],$_POST['data']['para']);
        echo 1;
    }
}
function api_change_username(){
    $user_id = get_current_user_id();
	if (is_wp_error(wp_authenticate(get_userdata(get_current_user_id())->user_login,$_POST['data']['password']))){
		echo '6';

		exit();
	}
	else if (get_userdata(get_current_user_id())->user_login == $_POST['data']['new_username']){
		echo '5';
	}
	else if (! username_possible($_POST['data']['new_username'])){
		echo '6';
		exit();
	}
	else{

		$wpdb->update($wpdb->users, array('user_login' => $_POST['data']['new_username']), array('ID' => $user_id));
		$wpdb->update($wpdb->users, array('user_nicename' => $_POST['data']['new_username']), array('ID' => $user_id));
		$creds = array(
		    'user_login' => $_POST['data']['new_username'],
		    'user_password' => $_POST['data']['password'],
		    'remember' => true
		);
		echo '1';
	}
}
function api_create_fandom(){
    $return = wp_insert_term($_POST['data']['fandom'],'category',array('parent'=>$_POST['data']['category']));
    echo is_wp_error($return);
}
function api_edit_update(){
    if(! is_user_logged_in()){
        echo 3;
    }
    else if (get_post($_POST['data']['update_id']) != null && get_post($_POST['data']['update_id'])->post_author == get_current_user_id() && get_post($_POST['data']['update_id'])->post_type == 'post' && isset($_POST['data']['action']) && ($_POST['data']['action'] == 'delete' || $_POST['data']['action'] == 'stick')){
        if ($_POST['data']['action'] == 'delete'){
            wp_delete_post($_POST['data']['update_id']);
            echo 1;
        }
        else if ($_POST['data']['action'] == 'stick'){
            if (is_sticky($_POST['data']['update_id'])){
                echo 11;
                unstick_post($_POST['data']['update_id']);
            }
            else{
                $posts = new WP_Query(array(
                    'post_type'      => array( 'post' ),
                    'orderby'        => 'modified',
                    'posts_per_page' => -1,
                    'author'         => get_current_user_id()
                ));
                foreach($posts->posts as $post){
                    unstick_post($post->ID);
                }
                stick_post($_POST['data']['update_id']);
                echo 12;
            }
        }
        else{
            echo 6;
        }
    }
    else if (isset($_POST['data']['update']) && $_POST['data']['update'] == ''){
        echo 6;
    }
    else if ($_POST['data']['update_id'] == 'new'){
	    $new_post = array(
	        'post_title'    	=> 'None',
	        'post_content'  	=> htmlspecialchars($_POST['data']['update']),
	        'post_status'   	=> 'publish',
	    );
        wp_insert_post($new_post);
        echo 2;
    }
    else if (get_post($_POST['data']['update_id']) != null && get_post($_POST['data']['update_id'])->post_author == get_current_user_id()){
        /**$edit_post = array(
            'ID'                => $_POST['data']['update_id'],
            'post_content'      => htmlspecialchars($_POST['data']['update']),
        );
        wp_update_post($edit_post);
        echo 4;**/
        echo 6;
    }
    else{
    	echo 6;
    }

}
function api_follow_collection(){
    function return_code($code,$extra = 0){
        $_return = array(
            'code'			        =>	$code,
        );
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    $bools = array('false','true');
    if (
        ! isset($_POST['data']['email_ntfy'])  ||
        ! isset($_POST['data']['follow']) ||
        ! in_array($_POST['data']['email_ntfy'],$bools) ||
        ! in_array($_POST['data']['follow'],$bools)){
        return_code(7);
    }
    if(! is_user_logged_in(  )){
        return_code(6);
    }
    $notifications = 'no-email';
    if ($_POST['data']['email_ntfy'] === 'true'){
        $notifications = 'all';
    }
    if ($_POST['data']['follow'] === 'false'){
        $notifications = false;
    }
    
    $return = collection::add_follow($_POST['data']['collection_id'],get_current_user_id(),$notifications);
    
    if (err::is($return)){
        return_code(8);
    }
    return_code(1);
}
function api_load_page(){
    //Parse Page Chain
    define('page_chain_arr', array_filter(explode('/',$_POST['data']['page_chain']), function ($elem) {
        if ($elem != ""){
            return $elem;
        }
    }));
    $pages = array('write','profile','stats','messages','chat','settings','bookmarks','collections');
    if (! in_array(page_chain_arr[0],$pages)){
        get_template_part('dashboard/part','dashboard');
    }
    else{
        get_template_part('dashboard/part',page_chain_arr[0]);
    }
}
function api_post_comment(){
    function return_code($code){
        $_return = array(
            'code'			=>	$code,
        );
        echo json_encode($_return);
        exit();
    }
    global $post;
    $post = get_post($_POST['data']['chapter_id']);
    if (! $post){
        return_code(7);
    }
    if ($_POST['data']['action'] == 'insert'){
        reviews::new(array(
            'chapter_id'    => $_POST['data']['chapter_id'],
            'review'        => $_POST['data']['comment']
        ));
    }
    else if ($_POST['data']['action'] == 'delete'){
        reviews::delete($_POST['data']['id']);
    }
    else if ($_POST['data']['action'] == 'reply'){
        reviews::new(array(
            'chapter_id'    => $_POST['data']['chapter_id'],
            'reply_to'      => $_POST['data']['id'],
            'review'        => $_POST['data']['comment']
        ));
    }
    ob_start();
    get_template_part('comments');
    $new_comments = ob_get_contents();
    ob_end_clean();
    echo json_encode(array(
        'code'      => 1,
        'comments'  => $new_comments
    ));
}
function api_post_survey(){
    if (
        (! isset($_POST['data']['rating']) || ! $_POST['data']['rating']) &&
        ( ! isset($_POST['data']['suggestion'])  || ! $_POST['data']['suggestion'])
    ){
        echo json_encode(array(
            'code'  => 7,
        ));
        exit();
    }
    
    $max_rating = 5;
    
    $vfs = _landing::vfs();
    global $wpdb;
    $wpdb->insert(
        'surveys', 
        array(
            'vfs' => $vfs,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('timestamp',true),
            'type'      => 'general',
            'rating' => isset($_POST['data']['rating']) ? ($_POST['data']['rating']/$max_rating)*100 : null,
            'suggestion' => isset($_POST['data']['suggestion']) && $_POST['data']['suggestion'] ? $_POST['data']['suggestion'] : null,
            'email' => isset($_POST['data']['email']) && $_POST['data']['email'] ? $_POST['data']['email'] : null,
        )
    );
    $survey_id = $wpdb->insert_id;
    echo json_encode(array(
        'code'  => 1,
    ));
}
function api_save_search(){
    if (isset($_POST['data']['to_delete'])){
        delete_search($_POST['data']['prev']);
        if (saved_search_exists('',$_POST['data']['prev']) == false){
            echo 2;
            exit();
        }
    }
    if (! is_user_logged_in()){
        echo 3;
        exit();
    }
    $exists = saved_search_exists($_POST['data']['name'],$_POST['data']['prev']);
    if ($_POST['data']['name'] == ''){
        echo 'Name cannot be empty.';
        exit();
    }
    else if ($exists){
        echo $exists;
        exit();
    }
    $save_search_r = save_search($_POST['data']['name'],$_POST['data']['prev']);
    if ($save_search_r != false){
        echo 1;
        exit();
    }
    else{
        //output false
        echo 'An error occured.';
        exit();
    }
}
function api_search_book_contents(){
	$s = $_POST['data']['s'];
    $book = get_post(get_post(intval($_POST['data']['chapter_id']))->post_parent);
    $link = get_permalink( $book );
    $results = [];
    if (stripos($book->post_title,$s) !== false){
        $results[] = array(
            'title'         => 'Book',
            'excerpt'       => mark_search($book->post_title,$s),
            'link'          => $link
        );
    }
    if (stripos($book->post_excerpt,$s) !== false){
        $results[] = array(
            'title'         => 'Book Summary',
            'excerpt'       => mark_search($book->post_excerpt,$s),
            'link'          => $link
        );
    }
    $chapters = published_chapters($book->ID);
    foreach ($chapters as $key => $chapter ) {
        if (count($results) > 80){
            $exceeded = true;
        break;
        }
        $chapter_num = $key+1;
        $title = $chapter_num . '. ' . $chapter->post_title;
        $link = get_permalink( $chapter );
        if (stripos($chapter->post_title,$s) !== false){
            $results[] = array(
                'title'         => $title,
                'excerpt'        => mark_search($chapter->post_title,$s),
                'link'          => $link
            );
        }
        $paras = explode('</p>', $chapter->post_content);
        foreach ($paras as $key => $para) {
            $para_num = $key+1;
            $para = ltrim($para, '<p>');
            if (stripos($para,$s) !== false){
                $results[] = array(
                    'title'     => $title,
                    'excerpt'    => mark_search($para,$s,300),
                    'link'      => $link . '#' . $para_num
                );
            }
        }
    }
    echo json_encode(array(
        'results'   => $results,
        'exceeded'  => $exceeded ?? false
    ));
    exit();
}
function api_search() {
    global $book_query;
    $book_query = new book_query;
    if (
        isset($_POST['data']['page'])
        && is_numeric($_POST['data']['page'])
    ){
        $book_query->args = ctrk_decrypt($_POST['data']['prev'],true);
        $book_query->args['page'] = $_POST['data']['page'];
    }
    else{
        $book_query->args_from_url($_POST['data']['search']);
        $placeholder = ctrk_decrypt($_POST['data']['placeholder'],true);
        $book_query->args = type_args($book_query->args,$placeholder);
    }
    $book_query->query();
    $response = array(
        'prev'      => ctrk_encrypt($book_query->args),
        'tags_data' => tags_data($book_query),
    );
    ob_start();
    ?>
    <book_collections hidden>
        <?= json_encode(collection::query_by_book(array_column($book_query->books,'ID'),'ID')); ?>
    </book_collections>
    <?php
	if ( $book_query->has() ){
        global $book;
        foreach ($book_query->books as $book) {
            get_template_part( 'template-parts/content' , 'search' );
        }
	}
	else {
		get_template_part( 'template-parts/content', 'noresult' );
    }
	$response['output'] = ob_get_contents();
    ob_end_clean();
	ob_start();
	get_template_part( 'template-parts/content', 'bookpaginate' );
	$response['paginate'] = ob_get_contents();
    ob_end_clean();
    $response['query'] = $book_query;
    echo json_encode($response);
}
function api_send_mail(){
    $message = 'Label- ' . $_POST['data']['label'] . "\r\n\r\n";
    $message .= 'Message From: ' . htmlspecialchars($_POST['data']['email']) . "\r\n\r\n";
	if (get_current_user_id() != 0){
		$message .= 'Author: ' . home_url('author/') . get_the_author_meta('user_nicename',get_current_user_id()) . "\r\n\r\n";
	}
	$message .= 'Message: ' . "\r\n\r\n";
	$message .= htmlspecialchars($_POST['data']['message']) . "\r\n\r\n";
	echo wp_mail('info@fanfiction.online','Message from Fanfiction Online',$message);

}
function api_set_password(){

    if (strlen($_POST['data']['password']) < 8){
        echo '2';
        exit();
    }
    $user = check_password_reset_key($_POST['data']['key'],$_POST['data']['login']);
    if (is_wp_error($user)){
        echo '0';
    }
    else{
        echo '1';
        $return = reset_password($user,$_POST['data']['password']);
        collection::create_default($user->ID);
    }
}
function api_update_profile(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    $validation = (new v_user(
        $_POST['data'],
        array(
            'email'
        )
    ))->return;
    if ($validation !== true){
        return_code(10);
    }
    $existing_user = get_user_by('ID', get_current_user_id() );

    $new_email_set = $existing_user->user_email !== $_POST['data']['email'];

	$userdata = array(
	    'ID'            => $existing_user->ID,
	    'description'   => htmlspecialchars($_POST['data']['about']),
    );
    if (isset($_POST['data']['password']) && $_POST['data']['password'] !== ''){
        $userdata['user_pass'] = $_POST['data']['password'];
    }
    wp_update_user($userdata);
    
	if ( $new_email_set ){
        mail_user::change_email($_POST['data']['email']);
	    return_code(1);
	}
	else{
	    delete_user_meta( $existing_user->ID, '_new_email' );
	    return_code(2);
	}
}
function api_validate_email(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    if (
        $_POST['data']['email'] ===
        get_the_author_meta('user_email',get_current_user_id())
    ){
        return_code(1);
    }
    $validation = (new v_user())->email($_POST['data']['email']);
    if ($validation->has()){
        return_code(9);
    }
    else{
        return_code(2);
    }
}
function api_validate_login(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    $username = $_POST['data']['username'];
    $pass = $_POST['data']['password'];
	$result = wp_authenticate($username,$pass);
    if (is_wp_error($result)){
        return_code(7);
    }
    else{
		$user = wp_signon( array(
            'user_login'    => $username,
            'user_password' => $pass,
            'remember'      => true
        ), true);
        update_user_meta($user->ID,'user_ip',$_SERVER['REMOTE_ADDR']);
        return_code(1);
    }

}
function api_validate_forgot(){
	function retrieve_password($user_login) {
		$errors = new WP_Error();

		if ( empty( $user_login ) || ! is_string( $user_login ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or email address.' ) );
		} elseif ( strpos( $user_login, '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $user_login ) ) );
			if ( empty( $user_data ) ) {
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no account with that username or email address.' ) );
			}
		} else {
			$login     = trim( $user_login );
			$user_data = get_user_by( 'login', $login );
		}

		/**
     * Fires before errors are returned from a password reset request.
     *
     * @since 2.1.0
     * @since 4.4.0 Added the `$errors` parameter.
     *
     * @param WP_Error $errors A WP_Error object containing any errors generated
     *                         by using invalid credentials.
     */
		do_action( 'lostpassword_post', $errors );

		if ( $errors->has_errors() ) {
			return $errors;
		}

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: There is no account with that username or email address.' ) );
			return $errors;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key        = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
         * The blogname option is escaped with esc_html on the way into the database
         * in sanitize_option we want to reverse this for the plain text arena of emails.
         */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
		/* translators: %s: Site name. */
		$message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
		/* translators: %s: User login. */
		$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		/* translators: Password reset notification email subject. %s: Site title. */
		$title = sprintf( __( '[%s] Password Reset' ), $site_name );

		/**
     * Filters the subject of the password reset email.
     *
     * @since 2.8.0
     * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
     *
     * @param string  $title      Default email title.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
		$title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

		/**
     * Filters the message body of the password reset mail.
     *
     * If the filtered message is empty, the password reset email will not be sent.
     *
     * @since 2.8.0
     * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
     *
     * @param string  $message    Default mail message.
     * @param string  $key        The activation key.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );
		$wp_mail_r = wp_mail( $user_email, wp_specialchars_decode( $title ), $message );
		if ( $message && ! $wp_mail_r ) {
			$errors->add(
				'retrieve_password_email_failure',
				sprintf(
					/* translators: %s: Documentation URL. */
					__( '<strong>ERROR</strong>: The email could not be sent. Your site may not be correctly configured to send emails. <a href="%s">Get support for resetting your password</a>.' ),
					esc_url( __( 'https://wordpress.org/support/article/resetting-your-password/' ) )
				)
			);
			return $errors;
		}

		return true;
    }
    function return_code($code) {
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    $username = $_POST['data']['username'];
    if (username_exists($username) || email_exists($username)){
        retrieve_password($username);
        return_code(1);
    }
    return_code(1);
}
function api_validate_username(){
    function return_code($code){
        echo json_encode(array(
            'code'  => $code
        ));
        exit();
    }
    if (
        $_POST['data']['username'] ===
        get_the_author_meta('user_login',get_current_user_id())
    ){
        return_code(1);
    }
    $validation = (new v_user())->username($_POST['data']['username']);
    if ( $validation->has() ){
        return_code(9);
    }
    else{
        return_code(2);
    }
}
function api_validate_signup(){
    function return_code($code,$merge = array()){
        echo json_encode(array_merge(array(
            'code'  => $code
        ),$merge));
        exit();
    }
    $return = (new v_user($_POST['data'],array(
        'username',
        'email'
    )))->return;
    if (err::is($return)){
        return_code(7,array(
            'errors'     => array_column($return->errors,'error','name')
        ));
    }
    $user_id = register_new_user(
        $_POST['data']['username'],
        $_POST['data']['email']
    );
    collection::create_default($user_id);
    return_code(1);
}
function api_autosave_content(){
    
    if (! is_edit_valid($_POST['data']['chapter_id'],'chapter',$_POST['data']['book_id']) ){
        exit();
    }
    
    global $wpdb;
    $result = array(
        'content'        => trim($_POST['data']['content']),
        'timestamp'      => current_time('timestamp',true)
    );
    $chapter_id = $_POST['data']['chapter_id'] === 'new' ? 0 : $_POST['data']['chapter_id'];
    $autosaves = get_autosaves($_POST['data']['book_id'],$chapter_id);
    krsort($autosaves);
    if (in_array($result['content'],$autosaves)){
        echo json_encode($autosaves);
        exit();
    }
    $wpdb->insert(
        'custom_autosaves',
        array(
            'chapter_id'    => $chapter_id,
            'book_id'       => $_POST['data']['book_id'],
            'author'        => get_current_user_id(),
            'timestamp'     => $result['timestamp'], 
            'content'       => $result['content'],
        )
    );
    /*echo '<br>Last error<br>';
    print_r($wpdb->last_error);
    echo '<br>Show errors<br>';
    print_r($wpdb->show_errors);
    echo '<br>Last query<br>';
    print_r($wpdb->last_query);
    echo '<br>-----------<br>';
    $id = $wpdb->insert_id;*/
    $autosaves[$result['timestamp']] = $result['content'];
    krsort($autosaves);
    echo json_encode($autosaves);
}
function api_delete_book(){
    $book = get_post($_POST['data']['id']);
    if ($book == null || $book->post_type != 'book'){
        exit;
    }
	$chapters = get_posts(array(
	    'post_parent'   => $book->ID,
		'post_type'		=> 'chapter',
		'sort_column'	=> 'post_modified',
		'post_status'	=> array('publish','draft','future'),
		'sort_order'	=> 'DESC',
		'posts_per_page'=> -1,
		'fields'        => 'ids'
	));
	foreach($chapters as $chapter_id){
        wp_update_post(array(
            'ID'    => $chapter_id,
            'post_status' => 'trash'
        ));
	}
    wp_update_post(array(
        'ID'    => $book->ID,
        'post_status' => 'trash'
    ));
    echo 1;
}
function api_get_chat(){
    function return_err(){
        echo json_encode(array(
            'messages' => false,
        ));
        exit();
    }
    $user = get_user_by( 'login', $_POST['data']['username'] );
    if ($user === false) {
        return_err();
    }
    $users_in_chat = array(
        intval( get_current_user_id() ),
        intval( $user->ID )
    );
    if ($users_in_chat[0] === $users_in_chat[1]){
        return_err();
    }
    $chats = chats::query(array(
        'users_included'  => $users_in_chat
    ));
    $read_chats = chats::query(array(
        'limit'             => -1,
        'users_included'    => $users_in_chat,
        'status_included'   => array('Read'),
        'fields'            => 'ids'
    ));
    $chats_f = array();
    foreach ( $chats as $chat ) {
        $chat_author = intval($chat->post_author);
        $this_chat_obj = array(
            'from'      => $users_in_chat[0] === $chat_author ? 'my' : 'other',
            'date'      => $chat->post_date,
            'message'   => $chat->post_content
        );
        if ($this_chat_obj['from'] === 'my'){
            $this_chat_obj['status'] = in_array($chat->ID,$read_chats) ? 'read' : 'sent';
        }
        $chats_f[] = $this_chat_obj;
        if ($chat_author !== $users_in_chat[0] && ! in_array($chat->ID,$read_chats)){
            wp_set_object_terms($chat->ID,'Read', 'message_status');            
        }
    }
    echo json_encode(array(
        'user'          => array(
                'name'      => $user->display_name
        ),
        'messages'      => array_reverse($chats_f),
        'blocked'       => chats::is_blocked(
                $users_in_chat[1],$users_in_chat[0]
                ) === true ? 'blocked' : '',
        'chat_blocked'  => chats::is_chat_blocked($users_in_chat)
    ));
}
function api_resend_dashboard(){
    get_template_part('dashboard/part','dashboarddiv');
}
function api_get_messages(){
    echo json_encode(chats::with());
}
function api_save_collection(){
    function return_code($code,$extra = 0){
        $_return = array(
            'code'			        =>	$code,
            'collection_data'		=>	collection::_present_dashboard()
        );
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    $existing_ = collection::query(array(
        'ids'   => array(intval($_POST['data']['collection_id']))
    ));
    if (! is_user_logged_in(  )){
        return_code(15);
    }
    if (empty($existing_) && $_POST['data']['collection_id'] !== 'new'){
        return_code(7);
    }
    if ($_POST['data']['collection_id'] !== 'new' && intval($existing_[0]['author']) !== get_current_user_id()){
        return_code(8);
    }
    if (! in_array($_POST['data']['action'],array('save','delete'))){
        return_code(16);
    }
    if ($_POST['data']['action'] === 'delete'){
        $return = collection::update($_POST['data']['collection_id'],array(
            'type'                 => 'Trash'
        ));
        return_code(3);
    }
    //Set Defaults
    $_POST['data']['description']   = $_POST['data']['description'] ?? '';
    $_POST['data']['title']         = $_POST['data']['title'] ?? '';

    if (! isset($_POST['data']['type']) || ! in_array($_POST['data']['type'],array('Public','Private','Unlisted'))){
        return_code(9);
    }
    if (strlen($_POST['data']['description']) > 400){
        return_code(11);
    }
    if ($_POST['data']['title'] === ''){
        return_code(12);
    }
    if (strlen($_POST['data']['title']) > 50){
        return_code(13);
    }
    $return = 0;
    $args = array(
        'title'                => $_POST['data']['title'],
        'description'          => $_POST['data']['description'],
        'type'                 => $_POST['data']['type'],
    );
    $collection_id = $_POST['data']['collection_id'];
    if (! in_array($existing_[0]['title'],array('Favorites','Hidden')) && $_POST['data']['collection_id'] !== 'new'){
        $return = collection::update($_POST['data']['collection_id'],$args);
    }
    else if ($_POST['data']['collection_id'] === 'new'){
        $return = collection::create($args);
        $collection_id = $return;
    }

    if (err::is($return)){
        if ($return->errors[0]['error'] == 'Author has collection with same slug.'){
            return_code(1001);
        }
        else if ($return->errors[0]['error'] == 'Slug exists.'){
            return_code(1002);
        }
        return_code(1000);
    }

    //Won't pass strict_author because check has already been done above
    $return = collection::set('collection',$collection_id,isset($_POST['data']['books']) ? $_POST['data']['books'] : array());
    if (err::is($return)){
        return_code(2000);
    }
    if ($_POST['data']['collection_id'] === 'new'){
        return_code(2);
    }
    return_code(1);
}
function api_save_settings(){
    update_user_meta(get_current_user_id(),'email_me_notifications',$_POST['data']['emails_notifications']);
}
function api_send_message(){
    $user = get_user_by( 'login', $_POST['data']['to'] );
    if ($user === false){
        echo json_encode(array(
            'sent'      => false
        ));
        exit();
    }
    // Add the content of the form to $post as an array
    $chat_obj = new chats($_POST['data']['message'],$user->ID);
    $success = true;
    if ($chat_obj->error->has()){
        $success = false;
    }
    echo json_encode(array(
        'sent'      => $success
    ));
}
function api_submit_book(){
    function return_code($code,$extra = 0){
        $_return = array(
            'code'			=>	$code,
            'book_id'		=>	$_POST['data']['book_id']
        );
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }

    $_POST['data'] = process_data($_POST['data'],'book');
    if (! $_POST['data']){
        return_code(9);
    }
    if ($_POST['data']['publish'] == 'true' && $_POST['data']['book_id'] == 'new'){
        return_code(10);
    }
    if ($_POST['data']['publish'] == 'true'){
        if (! can_publish($_POST['data'],'book')){
            return_code(11);
        }
        if (! isset($_POST['data']['selected_chapters'])){
            return_code(12);
        }
        foreach($_POST['data']['selected_chapters'] as $chapter_id){
            if (! can_publish_saved_chapter($chapter_id)){
                return_code(13);
            }
        }
        for ($i = 0; $i < count($_POST['data']['selected_chapters']); $i++) { 
            $chapter_id = $_POST['data']['selected_chapters'][$i];
            wp_publish_post( $chapter_id );
            update_post_meta($chapter_id,'chapter_order',$i + 1);
        }
    }
    if (! isset($_POST['data']['selected_chapters']) || $_POST['data']['publish'] == 'false'){
        $_POST['data']['selected_chapters'] = array();
    }
    $all_chapters = all_chapters($_POST['data']['book_id'],-1,'ids');
    $non_selected = array_diff($all_chapters,$_POST['data']['selected_chapters']);
    draft_these_chapters($_POST['data']['book_id']);

    // Add the content of the form to $post as an array
    $book = array(
        'post_title'    	=> htmlspecialchars($_POST['data']['title']),
        'post_content'  	=> htmlspecialchars($_POST['data']['detailed_description']),
        'post_type'			=> 'book',
        'post_excerpt'		=> htmlspecialchars($_POST['data']['description']),
        'post_status'		=> 'draft'
    );
    if ($_POST['data']['publish'] == 'true'){
        $book['post_status'] = 'publish';
    }

    //save the post
    if ($_POST['data']['book_id'] == 'new'){
        $book_id = wp_insert_post($book);
    }
    else{
        $book_id = $_POST['data']['book_id'];
        $book['ID'] = $book_id;
        wp_update_post($book);
    }
    update_post_meta($book_id,'anon_review',$_POST['data']['anonymous_reviews']);

    //Taxonomies
    //Simple taxonomies
    $taxonomies = array('tag','rating','language','status','genre');
    foreach($taxonomies as $taxonomy){
        wp_set_object_terms($book_id,$_POST['data'][$taxonomy], $taxonomy);
    }
    //Fandom
    wp_set_object_terms($book_id,$_POST['data']['fandom'],'category');


    //wp_set_object_terms() will be called outside loop for performance
    $characters_to_set = array();
    foreach($_POST['data']['characters'] as $fandom_id => $characters_arr){
        foreach($characters_arr as $character){
            if (term_exists($character,'character',$fandom_id) === null){
                wp_insert_term($character,'character',array(
                    'parent'	=> $fandom_id
                ));
            }
            $characters_to_set[] = $character;

        }
    }
    wp_set_object_terms($book_id,$characters_to_set, 'character');

    //Pairing
    $pairings_to_set = [];
    foreach($_POST['data']['pairing'] as $pairing){
        sort($pairing);
        $pairings_to_set[] = implode('/',$pairing);
    }
    wp_set_object_terms($book_id,$pairings_to_set,'pairing');

    $updated = get_post($book_id);
    if ($updated->post_status == 'publish'){
        $response = 1;
        wp_update_post(array(
            'ID'=> $book_id,
            'post_name' => htmlspecialchars($_POST['data']['title']),
        ));
    }
    else if($updated->post_status == 'draft'){
        $response = 2;
        wp_update_post(array(
            'ID'				=> $book_id,
            'post_name'			=> bin2hex(random_bytes(7)),
        ));
    }
    else{
        $response = 7;

    }
    echo json_encode( array( 'code' => $response, 'book_id' => $book_id));
}
function api_submit_chapter(){
    function return_code($code,$extra = 0){
        $_return = array(
            'code'			=>	$code,
            'chapter_id'	=>	$_POST['data']['chapter_id']
        );
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    if (! headers_sent() && ! isset($_SESSION) ){
        session_start();
    }
    $_POST['data'] = process_data($_POST['data'],'chapter');
    if (! $_POST['data']){
        return_code(9);
    }
    $book = get_post($_POST['data']['book_id']);
    if ($_POST['data']['publish'] == 'true'){
        if (! can_publish($_POST['data'],'chapter')){
            return_code(10);
        }
        if ($book->post_status != 'publish'){
            return_code(11);
        }
    }
    
    // Add the content of the form to $post as an array
    $new_post = array(
        'post_title'		=> htmlspecialchars($_POST['data']['title']),
        'post_content'  	=> $_POST['data']['content'],
        'post_type'			=> 'chapter',
        'post_status'   	=> 'draft',
        'comment_status'	=> 'closed',
        'post_parent'		=> $_POST['data']['book_id'],
    );
    if ($_POST['data']['publish'] == 'true'){
        $new_post['post_status'] = 'publish';
    }
    if ($_POST['data']['comments'] == 'true'){
        $new_post['comment_status'] = 'open';
    }
    if ($_POST['data']['chapter_id'] == 'new'){
        $chapter_id = wp_insert_post($new_post);
    }
    
    else{
        $chapter_id = $_POST['data']['chapter_id'];
        $new_post['ID'] = $chapter_id;
        wp_update_post($new_post);
        global $wpdb;
    }
    if ($_POST['data']['publish'] == 'true'){
        update_post_meta($chapter_id,'chapter_order',count(published_chapters($_POST['data']['book_id']))+1);
    }
    else{
        draft_these_chapters($_POST['data']['book_id'],array($chapter_id));
    }
    global $wpdb;
    $wpdb->delete( 'custom_autosaves', array( 'chapter_id' => $_POST['data']['chapter_id'],'book_id' => $_POST['data']['book_id']) );
    $updated = get_post($chapter_id);
    if ($updated->post_status == 'publish'){
        echo json_encode(array(
            'code'			=> 1,
            'chapter_id'	=> $chapter_id,
        ));
        exit();
    }
    else if($updated->post_status == 'draft'){
        if (empty(published_chapters($_POST['data']['book_id']))){
            wp_update_post(array(
                'ID'			=> $_POST['data']['book_id'],
                'post_status'	=> 'draft'
            ));
        }
        echo json_encode(array(
            'code'			=> 2,
            'chapter_id'	=> $chapter_id,
        ));
        
        exit();
    }
    return_code(7);
}
function api_manage(){
    if (! current_user_can( 'administrator' )){
        exit();
    }
    function _link($id,$type){
        if (in_array($type,array('home'))){
            $home_id = (new WP_Query(array(
                'name'			    => 'read',
                'title'             => 'Read',
                'post_type'		    => 'page',
                'posts_per_page'	=>  1,
                'fields'            => 'ids'
            )))->posts[0];
            return get_permalink($home_id);
        }
        else if (in_array($type,array('book','chapter','post','page'))){
            return get_permalink($id);
        }
        else if (in_array($type,array('term'))){
            return get_term_link($id);
        }
        else if (in_array($type,array('collection'))){
            return collection::link($id);
        }
        else if (in_array($type,array('author','user'))){
            return get_author_posts_url($id);
        }
        else if (in_array($type,array('survey'))){
            return '/manage/survey/' . $id;
        }
        else{
            return false;
        }
    }
    function _title($id,$type){
        if (in_array($type,array('home'))){
            $home_id = (new WP_Query(array(
                'name'			    => 'read',
                'title'             => 'Read',
                'post_type'		    => 'page',
                'posts_per_page'	=>  1,
                'fields'            => 'ids'
            )))->posts[0];
            return get_the_title($home_id);
        }
        else if (in_array($type,array('chapter'))){
            $obj = get_post($id);
            return $obj->post_title . ' - ' . get_the_title($obj->post_parent);
        }
        else if (in_array($type,array('book','post','page'))){
            return get_the_title($id);
        }
        else if (in_array($type,array('term'))){
            return get_term($id)->name;
        }
        else if (in_array($type,array('collection'))){
            return collection::query(array(
                'ids'   => array($id)
            ))[0]['title'];
        }
        else if (in_array($type,array('author','user'))){
            return get_the_author_meta('display_name',$id);
        }
        else if (in_array($type,array('survey'))){
            return 'Survey ' . $id;
        }
        else{
            return false;
        }
    }
        
    $vfs = $_POST['data']['vfs'];
    global $wpdb;
    $sql = $wpdb->prepare("
        SELECT * FROM stats_actions
        WHERE landing_id IN ( 
            SELECT ID FROM stats_landings
            WHERE vfs = %s
        )
        ORDER BY timestamp DESC
    ",array($vfs));
    $result = $wpdb->get_results ( $sql );
    $zero_result = array(
        'stat'      => $result[0]->stat,
        'type'      => $result[0]->type,
        'link'      => '<a href="' . _link($result[0]->type_id,$result[0]->type) . '">' . _title($result[0]->type_id,$result[0]->type) . '</a>',
        'timestamp' => $result[0]->timestamp
    );
    $return = [
        [//One for each session
            [//One for each type
                $zero_result
            ]
        ]
    ];
    foreach ($result as $key => $stat) {
        if ($key <= 0){
            continue;
        }
        $new_stat = array(
            'stat'      => $stat->stat,
            'type'      => $stat->type,
            'link'      => '<a href="' . _link($stat->type_id,$stat->type) . '">' . _title($stat->type_id,$stat->type) . '</a>',
            'timestamp' => $stat->timestamp
        );
        $prev_stat = $result[$key-1];
        if ($stat->type_id === $prev_stat->type_id &&
            $stat->type === $prev_stat->type &&
            $stat->timestamp - $prev_stat->timestamp < 60
            ){
                continue;
        }
        if (
            ($new_stat['timestamp'] - $prev_stat->timestamp) > 1800
        ){
            $return[] = array(
                array(
                    $new_stat
                )
            );
        }
        else if (
            $new_stat['stat'] == $prev_stat->stat &&
            $new_stat['type'] == $prev_stat->type
        ){
            $return[count($return)-1][count($return[count($return)-1])-1][] = $new_stat;
        }
        else{
            $return[count($return)-1][] = array(
                $new_stat
            );
        }

    }
    echo json_encode($return);
}
function api_poll(){
    function return_code($code,$extra = 0){
        $_return = array(
            'code'			=>	$code,
            'notifications'	=>  notifications::get()
        );
        if ($extra !== 0){
            $_return['extra'] = $extra;
        }
        echo json_encode($_return);
        exit();
    }
    $types = _landing::decrypt($_POST['data']['data']);
    if ($types === null){
        return_code(9);
    }
    $_POST['data']['im_books'] = isset($_POST['data']['im_books']) ? $_POST['data']['im_books'] : array();
    $_POST['data']['im_collections'] = isset($_POST['data']['im_collections']) ? $_POST['data']['im_collections'] : array();
    
    $instance = new _action($types->landing_id);
    foreach ($_POST['data']['im_books'] as $key => $book_id) {
        $instance->log_impression('book',$book_id);
    }
    foreach ($_POST['data']['im_collections'] as $key => $collection_id) {
        $instance->log_impression('collection',$collection_id);
    }
    $instance->log_view($types->type,$types->type_id);
    // if ($_POST['data']['notification_open'] === 'true'){
    //     $instance->log_notifications($types->type,$types->type_id);
    // }
    return_code(1);
}

if(! headers_sent() && ! isset($_SESSION) ){ 
    session_start(); 
}
if ( isset($_POST['reCAPTCHA']) ){
    if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        echo json_encode(array(
            'code'      => 997
        ));
        exit();
    }
}
else if (
    isset($_POST['nonce'])
    && isset($_SESSION['nonce'])
    && is_array($_SESSION['nonce'])
    && in_array($_POST['nonce'],$_SESSION['nonce'])
    ){}
else{
    echo json_encode(array(
        'code'  => 998
    ));
    exit();
}
//Call User Functions
if (! function_exists('api_' . $_POST['action'])){
    echo json_encode(array(
        'code'      => 999
    ));
    exit();
}
call_user_func('api_' . $_POST['action']);
exit();