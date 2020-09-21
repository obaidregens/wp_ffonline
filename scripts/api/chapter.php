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
function api_vote_story() {
    required_login();
    required_params('story_id');
    $story = get_post( $_POST['data']['story_id'] );
    if (!$story || $story->post_type !== 'book' || $story->post_status !== 'publish') {
        return ['code'=>8];
    }
    $exists = vote::exists('story',$story->ID);
    if (!$exists) {
        vote::new([
            'type'      => 'story',
            'type_id'   => $story->ID,
            'landing_id'=> $_POST['landing_id']
        ]);
        return ['code'=>1];
    }
    vote::unvote('story',$story->ID);
    return ['code'=>2];
}