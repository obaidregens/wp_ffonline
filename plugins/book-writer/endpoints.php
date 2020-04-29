<?php
/**
 * Endpoint Mask for Book Post Type.
 *
 * @since book-writer 2.0.2
 */
define( 'EP_BOOKS', 8192 );


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

    //Books
    add_rewrite_endpoint( 'chapter', EP_BOOKS,'chapter');
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


//<title>'s
add_filter( 'document_title_parts', function($title){
    global $wp_query;
    if (is_author()){
        if( array_key_exists( 'books', $wp_query->query_vars ) ){
            $title['title'] .= ' - Books';
        }
        else if( array_key_exists( 'updates', $wp_query->query_vars ) ){
            $title['title'] .= ' - Updates';
        }
        else if( array_key_exists( 'favorites', $wp_query->query_vars ) ){
            $title['title'] .= ' - Favorites';
        }
        else if( array_key_exists( 'collection', $wp_query->query_vars ) ){
            $title['title'] .= ' - Collections';
        }
    }
    else if( is_singular("chapter")){
        global $post;
        $title['title'] = 'Chapter ' . get_post_meta($post->ID,'chapter_order',true) . ' - ' . get_post($post->post_parent)->post_title . ' - by ' . get_the_author_meta('display_name',$post->post_author);
    }
    else if( is_singular("book")){
        global $post;
        $title['title'] = $post->post_title . ' - by ' . get_the_author_meta('display_name',$post->post_author);
    }
    else if (is_tax('collection')){
        $title['title'] .= ' - Collections';
    }
    return $title;
});
//Chapter
function ct_chapter_template($template = ''){
    global $post;
    global $wp_query;
    if ($post->post_type == 'book'){
        if (isset($wp_query->query_vars['chapter'])){
            $chapter = new WP_Query( array(
                'post_type'      => array( 'chapter' ),
                'post_parent'    => $post->ID,
                'meta_key'       => 'chapter_order',
                'meta_value' => intval($wp_query->query_vars['chapter']),
                'posts_per_page' => 1,
            ));
            if ($chapter->found_posts != 0){
                $post = $chapter->posts[0];
                $chapter = new WP_Query( array(
                    'post_type'      => array( 'chapter' ),
                    'p'             => $post->ID
                ));
                $wp_query = $chapter;
                $template = locate_template( array( 'single-chapter.php'), false );
            }
            else{
                $chapter = new WP_Query( array(
                    'post_type'      => array( 'chapter' ),
                    'p'              => 0
                ));
                $wp_query = $chapter;
                $wp_query->set_404();
                status_header( 404 );
                $template = locate_template( array( 'no-chapter.php', '404.php' ), false );
            }
        }
    }
    return $template;
}
add_filter( 'single_template', 'ct_chapter_template');
