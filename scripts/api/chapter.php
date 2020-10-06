<?php
function api_search_book_contents(){
	$s = $_POST['data']['s'];
    $book = get_post(get_post(intval($_POST['data']['chapter_id']))->post_parent);
    $link = get_permalink( $book );
    $results = [];
    if (stripos($book->post_title,$s) !== false){
        $results[] = array(
            'title'         => 'Story',
            'excerpt'       => mark_search($book->post_title,$s),
            'link'          => $link
        );
    }
    if (stripos($book->post_excerpt,$s) !== false){
        $results[] = array(
            'title'         => 'Story Summary',
            'excerpt'       => mark_search($book->post_excerpt,$s),
            'link'          => $link
        );
    }
    $chapters = published_chapters($book->ID);
    foreach ($chapters as $key => $chapter ) {
        if (count($results) > 80){
            $exceeded = true;
        break;
        }
        $chapter_num = $key+1;
        $title = $chapter_num . '. ' . $chapter->post_title;
        $link = get_permalink( $chapter );
        if (stripos($chapter->post_title,$s) !== false){
            $results[] = array(
                'title'         => $title,
                'excerpt'        => mark_search($chapter->post_title,$s),
                'link'          => $link
            );
        }
        $paras = explode('</p>', $chapter->post_content);
        foreach ($paras as $key => $para) {
            $para_num = $key+1;
            $para = ltrim($para, '<p>');
            if (stripos($para,$s) !== false){
                $results[] = array(
                    'title'     => $title,
                    'excerpt'    => mark_search($para,$s,300),
                    'link'      => $link . '#' . $para_num
                );
            }
        }
    }
    return [
        'results'   => $results,
        'exceeded'  => $exceeded ?? false
    ];
}
function api_vote_chapter() {
    required_login();
    required_params('chapter_id');
    $chapter = get_post( $_POST['data']['chapter_id'] );
    if (!$chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish') {
        return ['code'=>8];
    }
    if ( is_current_user($chapter->post_author) ){
        return ['code'=>11];
    }
    $exists = vote::exists('chapter',$chapter->ID);
    if (!$exists) {
        $return = vote::new([
            'type'      => 'chapter',
            'type_id'   => $chapter->ID,
            'landing_id'=> $_POST['landing_id']
        ]);
        if (err::is($return)) {
            return ['code'=>7];
        }
        return ['code'=>1];
    }
    vote::unvote('chapter',$chapter->ID);
    return ['code'=>2];
}
function api_offline_chapter() {
    required_params('chapter_id');
    $d = &$_POST['data'];
    $chapter = get_post($d['chapter_id']);
    if (!$chapter || $chapter->post_type !== 'chapter' || $chapter->post_status !== 'publish' ) {
        return ['code'=>10];
    }
    $chapter_num = get_post_meta( $chapter->ID, 'chapter_order', true );
    offline_stats::new([
        'type'          => 'offline',
        'landing_id'    => $_POST['landing_id'],
        'story_id'      => $chapter->post_parent,
        'chapter_id'    => $chapter->ID,
        'chapter_num'   => $chapter_num,
        'stat_millitime'=> millitime(),
    ]);
    return ['code'=>1];
}