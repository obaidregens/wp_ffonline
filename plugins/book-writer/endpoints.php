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
    // Favorites will be a subsection of collections
    // add_rewrite_endpoint( 'favorites', EP_AUTHORS);
    $parms = array('write','profile','stats','messages','chat','settings','bookmarks');
    foreach ($parms as $key => $value) {
        add_rewrite_endpoint( $value, EP_PAGES,false);
    }

    //Books
    add_rewrite_endpoint( 'chapter', EP_BOOKS,'chapter');

    //Collections
    add_rewrite_endpoint( 'collections', EP_AUTHORS | EP_PAGES | EP_ROOT, 'collections');
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
    if( array_key_exists( 'collections', $wp_query->query_vars ) ){
        $args = array(
            'slug'                  => $wp_query->query_vars['collections'],
            'types'                 => array('Favorites'),
            'text_search_protocol'  => 'strict',
            'authors'               => array($wp_query->queried_object->ID),
        );
        if (get_current_user_id() === $wp_query->queried_object->ID){
            $args['types'][] = 'Private';
            $args['types'][] = 'Unlisted';
        }
        $collection_query = collection::query($args);
        if ($wp_query->query_vars['collections'] == ''){
            $template = locate_template('author/collections.php');
        }
        else if (empty($collection_query)){
            _404('collections/404.php');
        }
        else{
            global $collection;
            $collection = $collection_query[0];
            $template = locate_template( 'collections/single.php' );
        }
    }
    return $template;
}
add_filter( 'author_template', 'wpa_author_template' );


//<title>'s
add_filter( 'document_title_parts', function($title){
    global $wp_query;
    if (is_author()){
        $author_name = $title['title'];
        if( array_key_exists( 'books', $wp_query->query_vars ) ){
            $title['title'] = 'Books - ' .$author_name;
        }
        else if( array_key_exists( 'updates', $wp_query->query_vars ) ){
            $title['title'] = 'Updates - ' . $author_name;
        }
        else if( array_key_exists( 'collections', $wp_query->query_vars ) ){
            global $collection;
            if ($wp_query->query_vars['collections'] == ''){
                $title['title'] = 'Collections - ' . $author_name;
            }
            else if (! isset($collection)){
                $title['title'] = 'Collection not found - ' . $author_name;
            }
            else{
                $title['title'] = $collection['title'] . ' - ' . 'Collections - ' . $author_name;
            }
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
    else if (isset($wp_query->query_vars['collections'])){
        global $collection;
        if ($wp_query->query_vars['collections'] === ''){
            $title['title'] = 'Collections';
        }
        else if (! isset($collection)){
            $title['title'] = 'Collection not found';
        }
        else{
            $title['title'] =  $collection['title'] . ' - Collections';
        }
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
                _404('no-chapter.php');
            }
        }
    }
    return $template;
}
add_filter( 'single_template', 'ct_chapter_template');

function collections_hook($template = ''){
    global $wp_query;
    global $template;

    if (isset($wp_query->query_vars['collections'])){
        $collection_query = collection::query(array(
            'types'                 => array('Public','Unlisted'),
            'slug'                  => $wp_query->query_vars['collections'],
            'text_search_protocol'  => 'strict',
        ));
        if ($wp_query->query_vars['collections'] == ''){
            $template = locate_template(array('collections/index.php'),false);
        }
        else if (empty($collection_query)){
            _404('collections/404.php');
        }
        else{
            global $collection;
            $collection = $collection_query[0];
            $template = locate_template( array('collections/single.php'),false);
        }
    }

    return $template;
}
add_filter( 'home_template', 'collections_hook' );