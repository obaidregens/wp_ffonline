<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if (! headers_sent() && ! isset($_SESSION) ){
	session_start();
}
if(! isset($_SESSION['nonce_key']) || !isset($_POST['ajax']) || $_POST['ajax'] != $_SESSION['nonce_key'] ){
    exit();
}

if (! is_edit_valid($_POST['chapter_id'],'chapter',$data['book_id']) ){
    exit();
}


global $wpdb;
$result = array(
    'content'        => trim($_POST['content']),
    'timestamp'      => current_time('timestamp',true)
);
$autosaves = get_autosaves($_POST['book_id'],$_POST['chapter_id']);
if (in_array($result['content'],$autosaves)){
    echo json_encode($autosaves);
    exit();
}
$wpdb->insert(
    'custom_autosaves',
    array(
        'chapter_id'    => $_POST['chapter_id'],
        'book_id'       => $_POST['book_id'],
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
echo json_encode($autosaves);
exit();