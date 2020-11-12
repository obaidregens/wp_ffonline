<?php
// Draft Editor APIS
function api_save_draft() {
    $d = &$_POST['data'];
    required_login();
    required_params('title','draft_id','content','perm');

    if (!is_array($d['content'])) {
        return ['code'=>13];
    }
    $words = str_word_count(drafts_json::simpleText($d['content'],true)) . " Words";
    $d['content'] = json_encode($d['content']);
    if ($d['content'] === false) {
        return ['code'=>14];
    }

    // Prepared Data
    $insert = [
        'title'     => $d['title'],
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

    // Will be adding a session variable that records when a perm is passed.
    // The next send will be push
    $session_perm = &$_SESSION['drafts'][$d['draft_id']]['prev_perm'];
    $flag = ($session_perm ?? false) ? 'push' : 'update';
    $session_perm = $d['perm'] === true;
    $time = draft_revision::push($draft_id,$d['content'],$flag );
    if (err::is($time)) {
        return ['code' => 13];
    }
    global $wpdb;
    return [
        'code'          => 1,
        'draft_id'      => $draft_id,
        'time'          => $time,
        'perm'          => $flag === 'push',
        "words"         => $words
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
    if (! is_current_user($draft->user_id)){
        return ['code'=>10];
    }
    $share = $d['share'] === true ? sha1(bin2hex(random_bytes(11))) : null;
    $valid = drafts::update($draft->ID,[
        'share' => $share
    ]);
    if ($d['share'] === true){
        return ['code'=>1,'link'=>home_url( '/drafts/' . $share )];
    }
    return ['code'=>2];
}
function api_get_draft_revisions() {
    required_login();
    required_params('draft_id');
    $d = &$_POST['data'];
    $draft = drafts::get_by('ID',$d['draft_id']);
    if (
        $draft === false
        || ! is_current_user($draft->user_id)
    ) {
        return ['code' => 12];
    }
    $revisions = draft_revision::getAll($d['draft_id']);
    array_shift($revisions);
    return [
        'code'      => 1,
        'revisions' => $revisions
    ];
}
function api_delete_draft () {
    required_login();
    required_params('draft_id');
    // Validation for this is being done inside the class
    $rows = drafts::delete($_POST['data']['draft_id']);
    return [
        'code'  => 1
    ];
}
function api_compare_single_revision () {
    required_login();
    required_params('draft_id','revision_id');
    $d = &$_POST['data'];
    $draft = drafts::get_by('ID',$d['draft_id']);
    if (
        $draft === false
        || ! is_current_user($draft->user_id)
    ) {
        return ['code' => 12];
    }
    $revisions = draft_revision::getAll($d['draft_id'],true);
    foreach ($revisions as $k => $rev ) {
        $isReq = intval($d['revision_id']) === intval($rev->ID);
        if ($isReq) {
            $new = $rev->content;
            $old = $revisions[$k+1]->content ?? json_encode([]);
        break;
        }
    }
    if (! isset($new)) {
        return ['code' => 8];
    }
    $compare = drafts_json::compare($old,$new);
    return [
        'code'      => 1,
        'compare'   => $compare,
        'revision'  => $new
    ];
}
function api_get_synonym() {
    required_params('word');
    $dict = new dict($_POST['data']['word']);
    return ['code' => 1,'synonyms' => $dict->synonym()];
}
function api_get_stories() {
    required_login();
    $q = (new WP_Query([
        'post_type'              => array( 'book' ),
        'post_status'            => array( 'publish' ),
        'posts_per_page'		 => -1,
        'author__in'             => [get_current_user_id()]
    ]))->posts;
    $stories = [];
    foreach ($q as $k => $story) {
        $stories[] = [
            'ID'        => $story->ID,
            'title'     => $story->post_title,
            'fandom'    => implode('/',array_column(wp_get_object_terms( $story->ID, 'category' ),'name')),
            'status'    => $story->post_status === 'publish' ? 'Published' : 'Unpublished',
        ];
    }
    return ['code'=>1,'stories'=>$stories];
}
function api_publish_to_story() {
    required_login();
    required_params('chapter_title','draft_id','storyID');
    $d = &$_POST['data'];
    $story = story::get($d['storyID'],true,false);
    if (! $story ){
        return ['code'=>7];
    }
    if (trim($d['chapter_title']) === "") {
        return ['code'=>8];
    }
    $chapter_id = draft_chapters::save(
        $d['draft_id'],
        $story->ID,
        substr($d['chapter_title'],0,80),
        ['pre'=>($d['preAN'] ?? ""),'post'=>($d['postAN'] ?? "")]
    );
    if ($chapter_id === false) {
        return ['code'=>9];
    }
    $chapter_num = count(published_chapters($story->ID,-1,'ids'));
    update_post_meta( $chapter_id, 'chapter_order', $chapter_num );
    return ['code'=>1,'new_chapter_link'=>'/story/'.$story->ID.'/'.$chapter_num];
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
function api_compare_draft () {
    required_params("share","draft_id","compare_with");
    $d = &$_POST['data'];
    $draft = drafts::get_by('ID',$d['draft_id']);
    $compare_with = drafts::get_by('ID',$d['compare_with']);
    if (!$draft || !$compare_with) {
        return ['code' => 9];
    }
    if (!is_current_user($compare_with->user_id)) {
        return ['code' => 10];
    }
    if (! (is_current_user($draft->user_id) || $draft->share === $d['share']) ) {
        return ['code' => 8];
    }
    $compare = drafts_json::compare($draft->content,$compare_with->content);
    return [
        'code'      => 1,
        'compare'   => $compare
    ];
}

// Index
function api_get_drafts() {
    $words = (bool) ($_POST['data']['words'] ?? false);
    return [
        'code'  => 1,
        'path'  => drafts_dir::get_path($words)
    ];
}
function api_create_drafts_folder() {
    required_login();
    required_params('name','path');
    $d = &$_POST['data'];
    $name = $d['name'];
    $f = drafts_dir::create_dir($name,$d['path']);
    return err::is($f) ? ['code'=>8,'error_message'=>$f->errors[0]['error']] : [
        'code'      => 1,
        'drafts'    => drafts_dir::get_path()
    ];
}
function api_move_draft () {
    required_login();
    required_params('draft_id','path');
    $d = $_POST['data'];
    $r = drafts::move($d['draft_id'],$d['path']);
    if (err::is($r)) {
        return ['code' => 9];
    }
    return ['code' => 1];
}
function api_delete_draft_dir () {
    required_login();
    required_params('path');
    // Validation for this is being done inside the class
    drafts_dir::delete($_POST['data']['path']);
    return [ 'code'  => 1 ];
}