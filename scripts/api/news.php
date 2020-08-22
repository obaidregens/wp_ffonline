<?php
function api_post_news() {
    required_admin();
    required_params('draft_id');
    $d = &$_POST['data'];
    $draft = drafts::get_by('ID',$d['draft_id']);
    if ($draft === false || ! is_current_user($draft->user_id)) {
        return ['code' => 9];
    }
    wp_insert_post(array(
        'post_title'    	=> 'None',
        'post_content'  	=> drafts_json::read($draft->content),
        'post_status'   	=> 'publish',
    ));
    return ['code' => 1];
}