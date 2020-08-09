<?php
function api_book_status() {
    required_login();
    required_params('book_id','status');
    function return_code($code) {
        echo json_encode([
            'code'      => $code
        ]);
        exit();
    }
    $d = &$_POST['data'];
    $book = get_post($d['book_id']);
    if ($book === null){
        return_code(7);
    }
    if ( intval($book->post_author) !== intval(get_current_user_id()) ){
        return_code(8);
    }
    $status = $d['status'] === "true" ? 'publish' : 'draft' ;
    global $wpdb;
    $wpdb->update('wp_posts',[
        'post_status'   => $status
    ],[
        'ID'        => $book->ID
    ]);
    return_code(1);
}
function api_review_status () {
    required_login();
    required_params('book_id','status');
    function return_code($code) {
        echo json_encode([
            'code'      => $code
        ]);
        exit();
    }
    $d = &$_POST['data'];
    $book = get_post($d['book_id']);
    if ($book === null){
        return_code(7);
    }
    if ( intval($book->post_author) !== intval(get_current_user_id()) ){
        return_code(8);
    }
    $status = $d['status'] === "true" ? 'open' : 'closed' ;
    global $wpdb;
    $wpdb->update('wp_posts',[
        'comment_status'    => $status
    ],[
        'ID'                => $book->ID
    ]);
    return_code(1);
}