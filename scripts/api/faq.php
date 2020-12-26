<?php
function api_ask_question() {
    required_params('question');
    $d = &$_POST['data'];
    $email = trim($d['email'] ?? "");
    if ( $email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        return ['code'=>7];
    }
    if(strlen(trim($d['question'])) <= 10){
        return ['code'=>8];
    }
    $e = questions::new([
        'email'         => $email,
        'landing_id'    => landing_id(),
        'for_user'      => 12,
        'question'      => $d['question'],
    ]);
    if (err::is($e)){
        return ['code'=>10];
    }
    if ($email === ""){
        return ['code'=>2];
    }
    return ['code'=>1];
}