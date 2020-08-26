<?php
// Draft Editor APIS
function api_save_draft() {
    $d = &$_POST['data'];
    required_login();
    required_params('title','draft_id','content','prevWasPerm');

    // Prepared Data
    $insert = [
        'title'     => stripslashes($d['title']),
    ];
    // If Draft is new, save to DB
    if ($d['draft_id'] === 'new') {
        $draft_id = drafts::new($insert);
        if (err::is($draft_id)) {
            return ['code' => 12];
        }
    }
    else {
        // If draft is old, check if session data exists & matches data
        // Else insert to DB
        $session_data = &$_SESSION['drafts'][$d['draft_id']]['data'];
        $draft_id = $d['draft_id'];
        if (! isset($session_data) || $session_data !== $insert ) {
            $is_in_session = false;
            $draft_id = drafts::update($d['draft_id'],$insert);
            if (err::is($draft_id)) {
                return ['code' => 13];
            }
            $session_data = $insert;
        }
    }
    // Now that Draft has been updated/cached let's push revisions.
    // Caching needs to be done for this as well but will be done in the class

    $flag = $d['prevWasPerm'] === 'true' ? 'push' : 'update';
    $time = draft_revision::push($draft_id,stripslashes($d['content']),$flag );
    if (err::is($time)) {
        return ['code' => 13];
    }
    global $wpdb;
    return [
        'code'          => 1,
        'draft_id'      => $draft_id,
        'time'          => $time
    ];
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
    $valid = drafts::update($draft->ID,[
        'share' => $share
    ]);
    if ($d['share'] === 'true'){
        return ['code'=>1,'link'=>home_url( '/drafts/' . $share )];
    }
    return ['code'=>2];
}
function api_get_draft_revisions() {
    required_login();
    required_params('draft_id');
    $d = &$_POST['data'];
    return [
        'code'      => 1,
        'revisions' => draft_revisions::for_draft($d['draft_id'])
    ];
}
function api_delete_draft () {
    required_login();
    required_params('draft_id');
    $rows = drafts::delete($_POST['data']['draft_id']);
    return [
        'code'  => 1,
    ];
}
// Preview
function api_edit_and_save_draft() {
    required_login();
    required_params('draft_id','share');
    $old_draft = drafts::get_by('ID',$_POST['data']['draft_id']);
    $share = $_POST['data']['share'];
    if ($old_draft === false ) {
        return ['code'=>11];
    }
    if ( is_current_user( $old_draft->user_id) ) {
        return ['code'=>1,'draft_id'=>$old_draft->ID];
    }
    if ($old_draft->share !== $share) {
        return ['code'=>9];
    }
    $draft_id = drafts::new([
        'title'     => $old_draft->title,
    ]);
    if (err::is($draft_id)){
        return ['code'=>10];
    }
    draft_revision::push($draft_id,$old_draft->content,'push');

    return ['code'=>1,'draft_id'=>$draft_id];
}

// Index
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