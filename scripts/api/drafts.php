<?php
function api_save_draft() {
    $d = &$_POST['data'];
    if (! isset($d['title']) || $d['title'] === ''){
        return ['code'=>8];
    }
    required_login();
    required_params('title','draft_id','content','path');
    // Valid
    $insert = [
        'title'     => stripslashes($d['title']),
        'content'   => stripslashes($d['content']),
    ];
    if ($d['path']) {
        $insert['path'] = strval($d['path']);
    }
    if ($d['draft_id'] !== 'new') {
        $insert['ID'] = $d['draft_id'];
        $draft = drafts::get_by('ID',$d['draft_id']);
        if ($draft === false) {
            return ['code'=>9];
        }
        if (intval($draft->user_id) !== intval(get_current_user_id())){
            return ['code'=>10];
        }
    }
    $valid = drafts::update($insert);
    if (err::is($valid)) {
        return [
            'code' => $valid->errors[0]['error'] === 'Draft with the same title already exists in this folder' ? 12 : 11
        ];
    }
    return ['code'=>1,'draft_id'=>$valid,'draft_path'=> drafts_dir::get_path()];
}
function api_share_draft() {
    required_login();
    required_params('draft_id','share');
    $d = &$_POST['data'];
    $draft = drafts::get_by('ID',$d['draft_id']);
    if ($draft === false) {
        return ['code'=>9];
    }
    if (intval($draft->user_id) !== intval(get_current_user_id())){
        return ['code'=>10];
    }
    $share = $d['share'] === 'true' ? bin2hex(random_bytes(11)) : null;
    $valid = drafts::update([
        'ID'    => $draft->ID,
        'share' => $share
    ]);
    if ($d['share'] === 'true'){
        return ['code'=>1,'link'=>home_url( '/drafts/' . $share )];
    }
    return ['code'=>2];
}
function api_edit_and_save_draft() {
    required_login();
    required_params('draft_id','share');
    $old_draft = drafts::get_by('ID',$_POST['data']['draft_id']);
    $share = $_POST['data']['share'];
    if ($old_draft === false ) {
        return ['code'=>11];
    }
    if ( intval($old_draft->user_id) === intval(get_current_user_id()) ){
        return ['code'=>1,'draft_id'=>$old_draft->ID];
    }
    if ($old_draft->share !== $share) {
        return ['code'=>9];
    }
    $valid = drafts::update([
        'title'     => $old_draft->title,
        'content'   => $old_draft->content
    ]);
    if (err::is($valid)){
        return ['code'=>10];
    }
    return ['code'=>1,'draft_id'=>$valid];
}
function api_compare_draft() {
    required_login();
    required_params('draft_id','share','compare');
    $current_user_id = intval(get_current_user_id());
    $old_draft = drafts::get_by('ID',$_POST['data']['compare']);
    $new_draft = drafts::get_by('ID',$_POST['data']['draft_id']);
    $share = $_POST['data']['share'];
    if ($old_draft === false || $new_draft === false ) {
        return ['code'=>11];
    }
    if ( intval($old_draft->user_id) !== $current_user_id ){
        return ['code'=>12];
    }
    if (intval($new_draft->user_id) !== $current_user_id && $new_draft->share !== $share){
        return ['code'=>9];
    }
    $x = drafts_json::compare($old_draft->content,$new_draft->content);
    return ['code'=>1,'compare_view'=>$x];

}
function api_delete_draft() {
    required_login();
    required_params('draft_id');
    $draft = drafts::get_by('ID',$_POST['data']['draft_id']);
    if ($draft === false || intval($draft->user_id) !== intval(get_current_user_id()) ) {
        return ['code'=>9];
    }
    drafts::delete($draft->ID);
    return ['code'=>1];
}
function api_autosave_draft () {
    required_login();
    required_params('title','draft_id','content');
    $d = &$_POST['data'];

    $draft = drafts::get_by('ID',$d['draft_id']);
    if ($d['draft_id'] !== 'new') {
        if ($draft === false) {
            return ['code'=>9];
        }
        if (! is_current_user($draft->user_id)){
            return ['code'=>10];
        }    
    }

    $insert = [
        'title'         => stripslashes($d['title']),
        'content'       => stripslashes($d['content']),
        'branch_type'   => 'autosave',
        'branch'        => $d['draft_id'] === 'new' ? 0 : $draft->ID
    ];
    $valid = drafts::update($insert,true);
    if ( err::is($valid) ) {
        return ['code'=>11];
    }
    $draft = draft_autosaves::get($valid);
    return ['code'=>1,'time'=>intval($draft->updated)];
}
function api_load_autosave() {
    required_login();
    required_params('draft_id');
    return ['autosave'=>draft_autosaves::for_draft($_POST['data']['draft_id'])];
}
function api_get_synonym() {
    required_params('word');
    $dict = new dict($_POST['data']['word']);
    return $dict->synonym();
}
function api_get_drafts() {
    return drafts_dir::get_path();
}
function api_create_drafts_folder() {
    required_login();
    required_params('name','path');
    $d = &$_POST['data'];
    $name = stripslashes($d['name']);
    $f = drafts_dir::create_dir($name,stripslashes($d['path']));
    return err::is($f) ? ['code'=>8,'error_message'=>$f->errors[0]['error']] : [
        'code'      => 1,
        'drafts'    => drafts_dir::get_path()
    ];
}