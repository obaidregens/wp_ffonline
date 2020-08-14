<?php
function api_save_draft() {
    function return_code($code, $id = null) {
        $rr = [
            'code'  => $code
        ];
        if ($id !== null) {
            $rr['draft_id'] = $id;
        }
        echo json_encode($rr);
        exit();
    }
    $d = &$_POST['data'];
    if (! isset($d['title']) || $d['title'] === ''){
        return_code(8);
    }
    required_login();
    required_params('title','draft_id','content');
    // Valid
    $insert = [
        'title'     => $d['title'],
        'content'   => stripslashes($d['content']),
    ];
    if ($d['draft_id'] !== 'new') {
        $insert['ID'] = $d['draft_id'];
        $draft = drafts::get_by('ID',$d['draft_id']);
        if ($draft === false) {
            return_code(9);
        }
        if (intval($draft->user_id) !== intval(get_current_user_id())){
            return_code(10);
        }
    }
    $valid = drafts::update($insert);
    if (err::is($valid)) {
        return_code(11);
    }
    return_code(1,$valid);
}
function api_share_draft() {
    required_login();
    required_params('draft_id','share');
    function return_code($code, $link = null) {
        $rr = [
            'code'  => $code
        ];
        if ($link !== null) {
            $rr['link'] = $link;
        }
        echo json_encode($rr);
        exit();
    }
    $d = &$_POST['data'];
    $draft = drafts::get_by('ID',$d['draft_id']);
    if ($draft === false) {
        return_code(9);
    }
    if (intval($draft->user_id) !== intval(get_current_user_id())){
        return_code(10);
    }
    $share = $d['share'] === 'true' ? bin2hex(random_bytes(11)) : null;
    $valid = drafts::update([
        'ID'    => $draft->ID,
        'share' => $share
    ]);
    if ($d['share'] === 'true'){
        return_code(1,home_url( '/drafts/' . $share ));
    }
    return_code(2);
}
function api_edit_and_save_draft() {
    required_login();
    required_params('draft_id','share');
    function return_code($code,$new_draft_id = null) {
        $rr = [
            'code'  => $code
        ];
        if ($new_draft_id !== null) {
            $rr['draft_id'] = $new_draft_id;
        }
        echo json_encode($rr);
        exit();
    }
    $old_draft = drafts::get_by('ID',$_POST['data']['draft_id']);
    $share = $_POST['data']['share'];
    if ($old_draft === false ) {
        return_code(11);
    }
    if ( intval($old_draft->user_id) === intval(get_current_user_id()) ){
        return_code(1,$old_draft->ID);
    }
    if ($old_draft->share !== $share) {
        return_code(9);
    }
    $valid = drafts::update([
        'title'     => $old_draft->title,
        'content'   => $old_draft->content
    ]);
    if (err::is($valid)){
        return_code(10);
    }
    return_code(1,$valid);

}
function api_delete_draft() {
    required_login();
    required_params('draft_id');
    function return_code($code) {
        $rr = [
            'code'  => $code
        ];
        echo json_encode($rr);
        exit();
    }
    $draft = drafts::get_by('ID',$_POST['data']['draft_id']);
    if ($draft === false || intval($draft->user_id) !== intval(get_current_user_id()) ) {
        return_code(9);
    }
    drafts::delete($draft->ID);
    return_code(1);
}