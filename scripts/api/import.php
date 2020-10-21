<?php
function api_import_stories() {
    required_login();
    $re = import_stories::import_request($_POST['data']['storyIds'] ?? []);
    if ($re === false) {
        return ['code' => 9];
    }
    return ['code'  => 1];
}
function api_disable_auto_update() {
    required_login();
    required_params("story_id");
    $story = get_post($_POST['data']['story_id']);
    if ( !$story || !is_current_user($story->post_author) || $story->post_type !== 'book' ) {
        return ['code'=>8];
    }
    $re = import_stories::cancel_auto_update($story->ID);
    if ($re === false) {
        return ['code'=>9];
    }
    return ['code'=>1];
}