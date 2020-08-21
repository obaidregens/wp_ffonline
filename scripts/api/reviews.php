<?php
function api_publish_review(){
    required_params('chapter_id');
    function return_code($code){
        $_return = array(
            'code'			=>	$code,
        );
        echo json_encode($_return);
        exit();
    }
    $d = &$_POST['data'];
    if (! reviews::can_review($d['chapter_id'])) {
        return_code(8);
    }
    if ($d['action'] === 'insert'){
        required_params('content');
        reviews::new(array(
            'chapter_id'    => $d['chapter_id'],
            'review'        => $d['content']
        ));
    }
    else if ($d['action'] == 'reply'){
        required_params('content','review_id');
        reviews::new(array(
            'chapter_id'    => $d['chapter_id'],
            'reply_to'      => $d['review_id'],
            'review'        => $d['content']
        ));
    }
    return_code(1);
}
function api_delete_review() {
    required_params('chapter_id','review_id');
    function return_code($code){
        $_return = array(
            'code'			=>	$code,
        );
        echo json_encode($_return);
        exit();
    }
    $d = &$_POST['data'];
    if (! reviews::can_review($d['chapter_id'])) {
        return_code(8);
    }
    reviews::delete($d['review_id']);
    return_code(1);
}
function api_get_reviews(){
    required_params('sort','chapter_id');
    $d = $_POST['data'];
    $sort = in_array($d['sort'],['DESC','ASC']) ? $d['sort'] : 'DESC';
    $chapter = get_post( $d['chapter_id'] );
    if ($chapter === false || $chapter->post_status !== 'publish' || $chapter->post_type !== 'chapter') {
        echo json_encode([
            'code'  => 13
        ]);
        exit();
    }
    $book = get_post( $chapter->post_parent );
    if ($book === false || $book->post_status !== 'publish' || $book->post_type !== 'book') {
        echo json_encode([
            'code'  => 14
        ]);
        exit();
    }

    $reviews = reviews::query([
        'order'         => $sort,
        'chapter'       => $chapter->ID,
        'page'          => $d['page'] ?? 1,
        'exclude_users' => $d['exclude_users'] ?? []
    ]);
    
    echo json_encode($reviews);
    exit();
}