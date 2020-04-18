<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax']) && isset($_POST['id']) ) {
    if (verify_reCAPTCHA($_POST['reCAPTCHA'])['success'] != true){
        $response = '8';
        echo json_encode(array('response'=>$response,'id'=>$_POST['id']));
        exit();
    }
    //Validation error code is '4'
    if ($_POST['id'] != 'new'){
        $old = get_post($_POST['id']);
        if ($old->post_author != get_current_user_id()){
            $error_exists = 1;
        }
    }
    if ($_POST['publish'] == 'publish'){
    	if (! isset($_POST['selected_chapters'])){
    	    $error_exists = 1;
    	}
    	if ($_POST['title'] == '' || $_POST['description'] == '' || strlen($_POST['title']) > 40 || strlen($_POST['description']) > 400){
    	    $error_exists = 1;
    	}
    	$req_taxonomies = array('category','rating','language','status','genre');
    	foreach($req_taxonomies as $tax){
    	    if ($_POST[$tax] == '' && $tax != 'genre'){
    	        $error_exists = 1;
    	        break;
    	    }
    	    foreach($_POST[$tax] as $term){
    	        if (! term_exists($term,$tax)){
    	            $error_exists = 1;
    	            break 2;
    	        }
    	    }
    	}
    	
    }
    if (isset($error_exists)){
        echo json_encode(array('response'=>'4','id'=>0));
    }
    if ($_POST['id'] != 'new'){
    	$chapters = get_posts(array(
    	    'post_parent'   => $_POST['id'],
    		'post_type'		=> 'chapter',
    		'sort_column'	=> 'post_modified',
    		'post_status'	=> array('publish','draft'),
    		'sort_order'	=> 'DESC',
    		'posts_per_page'=> -1,
    		'fields'        => 'ids'
    	));
    	if (! isset($_POST['selected_chapters'])){
    	    $_POST['selected_chapters'] = array();
    	}
        foreach ($chapters as $chapter_id){
            wp_update_post(array(
                'ID'    => $chapter_id,
                'post_status' => 'draft'
            ));
        }
        foreach ($_POST['selected_chapters'] as $chapter_id){
            wp_update_post(array(
                'ID'    => $chapter_id,
                'post_status' => 'publish'
            ));
        }
    }
    // Add the content of the form to $post as an array
    $post = array(
        'post_title'    	=> htmlspecialchars($_POST['title']),
        'post_content'  	=> htmlspecialchars($_POST['detailed_desc']),
        'post_status'   	=> $_POST['publish'],  
        'post_type'			=> 'book',
		'post_excerpt'		=> htmlspecialchars($_POST['description']),
    );
    //save the post
	if ($_POST['id'] == 'new'){
	   $post_id = wp_insert_post($post);
	}
	else{
	    $post['ID'] = $_POST['id'];
	    $post_id = $_POST['id'];
	    wp_update_post($post);
	}
    update_post_meta($post_id,'anon_review',$_POST['anon_review']);
	$taxonomies = array('tag','category','rating','language','status','genre','pairing');
	foreach($taxonomies as $taxonomy){
		wp_set_object_terms($post_id,$_POST[$taxonomy], $taxonomy);
	}
	$terms_char = array();
	foreach($_POST['characters'] as $key => $val){
		$parent = get_term_by('slug',$key,'category')->term_id;
		foreach($val as $term){
			if (term_exists($term,'character',$parent) === null){
				wp_insert_term($term,'character',array('parent'=>$parent));
			}
			$terms_char[] = $term;
		}
	}
	wp_set_object_terms($post_id,$terms_char, 'character');
	$updated = get_post($post_id);
	if ($updated->post_status == 'publish'){
		$response = '1';
    	wp_update_post(array(
            'ID'=> $post_id,
            'post_name' => $_POST['title'],
        ));
	}
	else if($updated->post_status == 'draft'){
		$response = '2';
    	wp_update_post(array(
            'ID'=> $post_id,
            'post_name' => '',
        ));
    	$chapters = get_posts(array(
    	    'post_parent'   => $post_id,
    		'post_type'		=> 'chapter',
    		'sort_column'	=> 'post_modified',
    		'post_status'	=> array('publish'),
    		'sort_order'	=> 'DESC',
    		'posts_per_page'=> -1,
    		'fields'        => 'ids'
    	));
    	foreach($chapters as $chapter_id){
        	wp_update_post(array(
                'ID'=> $chapter_id,
                'post_status' => 'draft',
            ));    	    
    	}
	}
	else{
		$response = '3';

	}
	echo json_encode(array('response'=>$response,'id'=>$post_id));
	exit();
}