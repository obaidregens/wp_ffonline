<?php
//returns boolean always
function is_edit_valid($postid,$type,$parentid = 0,$userid = 0){
    if ($userid == 0){
        $userid = get_current_user_id();
    }
    if (! in_array($type,array('book','chapter','post'))){
        return false;
    }
    $post = get_post($postid);
    if ($postid !== 'new' && $post === null){
        return false;
    }
    if ($postid != 'new'){
        if ($post->post_type != $type || ! in_array($post->post_status,array('publish','draft','future')) || $post->post_author != $userid){
            return false;
        }
    }
    if ($parentid != 0){
        $parent = get_post($parentid);
        if ($parent === null || ! in_array($parent->post_status,array('publish','draft','future')) || $parent->post_author != $userid){
            return false;
        }
        if ($postid != 'new'){
            if ($post->post_parent != $parentid){
                return false;
            }
        }
    }
    return true;
}

//$data => associative array consisting of data
//Will return false or processed $data
function process_data($data,$post_type){
    //Now, move onto type specific validation
    if ($post_type == 'chapter'){
        if (! isset($data['chapter_id']) || ! isset($data['book_id']) ){
            return false;
        }
        if (! is_edit_valid($data['chapter_id'],'chapter',$data['book_id']) ){
            return false;
        }
        if ( isset($data['content'])){
            //Check if content includes anything other than allowed through editor
            $data['content'] = ltrim(rtrim(strip_tags(stripcslashes($data['content']),'<p><strike><b><i><u>')));
            $regex = '/(((<p>)|(<p)([\s]*?)(style="text-align:)([\s]*?)(center|right|left)(;">))([\s\S]*?)(<\/p>))/i';
            if (strlen(preg_replace ($regex,'',$data['content'])) > 0){
                return false;
            }
        }
    }
    else if ($post_type == 'book'){     
        if (! isset($data['book_id']) ){
            return false;
        }
        if (! is_edit_valid($data['book_id'],'book') ){
            return false;
        }   
        //Check if term_id exists;
        $existing_taxonomies = array('category','fandom','rating','language','status','genre');
        foreach($existing_taxonomies as $taxonomy){
            if (! isset($data[$taxonomy])){
                continue;
            }
            $taxonomy_name = $taxonomy;
            if ($taxonomy == 'fandom'){
                $taxonomy_name = 'category';
            }
            foreach($data[$taxonomy] as $key => $term_id){
                $term_id = intval($term_id);
                $data[$taxonomy][$key] = $term_id;
                if (! term_exists($term_id,$taxonomy_name)){
                    return false;
                }
            }
        }
    }
    return $data;
}


//$data => associative array consisting of data
//Function also assumes $data has been processed & validated with process_data()
//Will always return boolean
function can_publish($data,$post_type){
    if ($post_type == 'chapter'){
        //$chapter = get_post($data['chapter_id]);
        //$book = get_post($data['book_id']);
        if (! isset($data['title']) || $data['title'] == ''){
            return false;
        }
        if (! isset($data['content']) || $data['content'] == ''){
            return false;
        }
    }
    else if ($post_type == 'book'){
        //$book = get_post($data['book_id']);
        if (! isset($data['title']) || $data['title'] == '' || strlen($data['title']) > 40){
            return false;
        }
        if (! isset($data['description']) || $data['description'] == '' || strlen($data['description']) > 400){
            return false;
        }
        $required_taxonomies = array('category','fandom','rating','language','status');
        foreach($required_taxonomies as $tax){
            if (! isset($data[$tax]) || empty($data[$tax]) ){
                return false;
            }
        }
    }
    return true;
}
//Wrapper for can_publish($data,'chapter')
//Takes in chapter_id instead of $data
function can_publish_saved_chapter($chapter_id){
    $chapter = get_post($chapter_id);
    if ($chapter == null){
        return false;
    }
    $data = array(
        'title'     => htmlspecialchars_decode($chapter->post_title),
        'content'   => $chapter->post_content
    );
    return can_publish($data,'chapter');
}

function published_chapters($book_id,$limit = -1,$fields = 'all'){
	$chapters = (new WP_Query(array(
        'post_parent'   => $book_id,
        'post_type'		=> 'chapter',
        'post_status'	=> array('publish'),
        'meta_key'		=> 'chapter_order',
        'orderby'		=> 'meta_value_num',
        'order'			=> 'ASC',
        'fields'        => $fields,
        'posts_per_page'=> $limit
    )))->posts;
    return $chapters;
}
function draft_chapters($book_id,$limit = -1,$fields = 'all'){
	$chapters = (new WP_Query(array(
        'post_parent'   => $book_id,
        'post_type'		=> 'chapter',
        'post_status'	=> array('draft'),
        'fields'        => $fields,
        'posts_per_page'=> $limit
    )))->posts;
    return $chapters;
}
function all_chapters($book_id,$limit = -1,$fields = 'all'){
	$chapters = (new WP_Query(array(
        'post_parent'   => $book_id,
        'post_type'		=> 'chapter',
        'post_status'	=> array('publish','draft','future'),
        'fields'        => $fields,
        'posts_per_page'=> $limit
    )))->posts;
    return $chapters;
}