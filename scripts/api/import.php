<?php
function api_import_stories() {
    required_login();
    $re = import_stories::import_request($_POST['data']['storyIds'] ?? []);
    if ($re === false) {
        return ['code' => 9];
    }
    return ['code'  => 1];
}
function api_import_status() {
    required_login();
    required_params("action","story_id");
    $a = $_POST['data']['action'];
    $story = get_post($_POST['data']['story_id']);
    if ( !$story || !is_current_user($story->post_author) || $story->post_type !== 'book' ) {
        return ['code'=>8];
    }
    $allow_reimport = in_array(intval(get_current_user_id()),get_option( 'reimport_allow', [] ));
    if ($a === "disable_autoupdate") {
        $re = import_stories::cancel_auto_update($story->ID);
        if ($re === false) {
            return ['code'=>9];
        }
    } else if ($a === "reimport") {
        if (!$allow_reimport){
            return ['code'=>14];
        }
        import_stories::set_re_import($story->ID,true);
    } else if ($a === "cancel_reimport") {
        import_stories::set_re_import($story->ID,false);
    } else {
        return ['code'=>10];
    }
    if ($re === false) {
        return ['code'=>9];
    }
    return ['code'=>1,'allow_reimport'=>$allow_reimport];
}