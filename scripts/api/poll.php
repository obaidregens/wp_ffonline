<?php
function api_get_poll() {
    required_params('poll_id');
    $d = &$_POST['data'];
    $poll = poll::get($d['poll_id']);
    if (!$poll) {
        return ['code'=>8];
    }
    if ( isset($poll->options[strval($d['voteOn'] ?? 0)]) ) {
        poll::vote($d['poll_id'],$d['voteOn']);
    } 
    $results = poll::results($d['poll_id']);
    $results['code'] = 1;
    $results['logged_in'] = is_user_logged_in();
    return $results;
}