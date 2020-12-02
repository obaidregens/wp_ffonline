<?php
function api_publish_review(){
    required_params('chapter_id','content','review_id');
    $d = &$_POST['data'];
    if (! reviews::can_review($d['chapter_id'])) {
        return ['code'=>8];
    }
    $len = strlen($d['content']);
    if ($len < 3) {
        return ['code'=>10];
    }
    // Check If Quote is Valid
    $quote = trim($d['quote']);
    if ($quote !== "") {
        $trimmed_quote = str_replace("\n","",strip_tags($quote));
        $content = str_replace("\n","",strip_tags(trim(get_post( $d['chapter_id'] )->post_content)));
        if (strpos($content,$trimmed_quote) === false) {
            return ['code'=>12];
        }
    }

    if ( strlen(strip_tags($d['content'])) < $len ) {
        spam::add("html_tags_in_review",$_POST['landing_id'],$d['content']);
    }
    if (intval($d['review_id']) === 0){
        reviews::new([
            'type'          => 'chapter',
            'type_id'       => $d['chapter_id'],
            'landing_id'    => $_POST['landing_id'],
            'quote'         => $quote,
            'review'        => $d['content']
        ]);
        return ['code' => 1];
    }
    reviews::new([
        'type'          => 'chapter',
        'type_id'       => $d['chapter_id'],
        'landing_id'    => $_POST['landing_id'],
        'reply'         => $d['review_id'],
        'review'        => $d['content']
    ]);
    return ['code' => 2];
}
function api_delete_review() {
    required_params('chapter_id','review_id');
    $d = &$_POST['data'];
    if (! reviews::can_review($d['chapter_id'])) {
        return ['code'=>8];
    }
    reviews::delete($d['review_id']);
    return ['code'=>1];
}
function api_get_reviews(){
    required_params('sort','chapter_id');
    $d = $_POST['data'];
    $sort = in_array($d['sort'],['DESC','ASC']) ? $d['sort'] : 'DESC';
    $chapter = get_post( $d['chapter_id'] );
    if ($chapter === false || $chapter->post_status !== 'publish' || $chapter->post_type !== 'chapter') {
        return ['code'=>13];
    }
    $book = story::get( $chapter->post_parent,false );
    if ( !$book && !(beta::is() && beta::can() && is_test_story($chapter))  ) {
        return ['code'=>14];
    }

    $reviews = reviews::queryBuild([
        'order'         => $sort,
        'chapter'       => $chapter->ID,
        'page'          => $d['page'] ?? 1,
        'exclude_users' => $d['exclude_users'] ?? []
    ]);
    
    return $reviews;
    exit();
}