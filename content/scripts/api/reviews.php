<?php
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