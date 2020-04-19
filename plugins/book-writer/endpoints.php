<?php
//source https://wordpress.stackexchange.com/questions/123210/how-to-set-up-author-archives-with-sub-category-url
function wpa_author_endpoints(){
    global $wp_query;

    add_rewrite_endpoint( 'books', EP_AUTHORS);
    add_rewrite_endpoint( 'updates', EP_AUTHORS);
    add_rewrite_endpoint( 'favorites', EP_AUTHORS);
    add_rewrite_endpoint( 'collection', EP_AUTHORS);
    $parms = array('write','profile','stats','edit-chapter','messages','chat','settings','edit-book','bookmarks','collections');
    foreach ($parms as $key => $value) {
        add_rewrite_endpoint( $value, EP_PAGES,false);
    }
}
add_action( 'init', 'wpa_author_endpoints' );
function wpa_author_template( $template = '' ){
    global $wp_query;
    if( array_key_exists( 'books', $wp_query->query_vars ) ){
        $template = locate_template( array( 'author/books.php', $template ), false );
	}
    if( array_key_exists( 'updates', $wp_query->query_vars ) ){
        $template = locate_template( array( 'author/updates.php', $template ), false );
	}
    if( array_key_exists( 'favorites', $wp_query->query_vars ) ){
        $template = locate_template( array( 'author/favorites.php', $template ), false );
	}
    if( array_key_exists( 'collection', $wp_query->query_vars ) ){
        $template = locate_template( array( 'author/collections.php', $template ), false );
    }
    return $template;
}
add_filter( 'author_template', 'wpa_author_template' );
