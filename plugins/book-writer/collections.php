<?php
function set_collections($book_id,$collections){
    $favorite_pos = array_search('favorites',$collections);
    $hidden_pos = array_search('hidden',$collections);
    if ($favorite_pos !== false){
        unset($collections[$favorite_pos]);
        add_favorite_book($book_id);
    }
    else{
        delete_favorite_book($book_id);
    }
    if ($hidden_pos !== false){
        unset($collections[$hidden_pos]);
        add_to_hidden($book_id);
    }
    else{
        delete_hidden($book_id);
    }
    wp_set_post_terms($book_id,$collections,'collection');
}