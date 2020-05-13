<?php
define('WP_USE_THEMES', false);
require(explode('wp-content',__FILE__)[0] . 'wp-load.php');

if( isset($_POST['ajax']) && isset($_POST['collection']) ) {
    if ($_POST['public_switch'] != 'Public'){
        $_POST['public_switch'] == 'Private';
        $slug = bin2hex(random_bytes(7));
    }
    else{
        $slug = $_POST['name'];
    }
    if (isset($_POST['to_delete'])){
        wp_delete_term($_POST['collection'],'collection');
        echo 1;
    }
	else if ($_POST['collection'] == 'favorites'){
		delete_favorite_book($_POST['deleted_books']);
		echo 1;
	}
	else if ($_POST['collection'] == 'hidden'){
		delete_hidden($_POST['deleted_books']);
		echo 1;
	}
    else if ($_POST['collection'] != 'new'){
        $return = wp_update_term($_POST['collection'],'collection',array(
            'name'  => $_POST['name'],
            'description'  => $_POST['description'],
            'slug'  => $slug
        ));
        update_term_meta($_POST['collection'],'public_collection',$_POST['public_switch']);
        if (isset($_POST['deleted_books']) && is_array($_POST['deleted_books'])){
            foreach($_POST['deleted_books'] as $book_id){
                wp_set_post_terms($book_id,array(),'collection');
            }
        }
        if (is_wp_error($return)){
            echo 0;
            exit();
        }
        echo 1;
    }
    else{
        $term_id = wp_insert_term($_POST['name'],'collection',array('description' => $_POST['description'],'slug' => $slug));
        if(is_wp_error($term_id)){
            echo 0;
            exit();
        }
        $term_id = $term_id['term_id'];
        update_term_meta($term_id,'author',get_current_user_id());
        update_term_meta($term_id,'time_created',current_time('timestamp'));
        update_term_meta($term_id,'public_collection',$_POST['public_switch']);
        echo 2;
    }
	exit();
}