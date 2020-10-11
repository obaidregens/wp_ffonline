<?php
//Non unique chapter slugs by chapter order
function chapter_post_name($slug,$post_ID,$post_status,$post_type,$post_parent,$original_slug ){
	if ($post_type == 'chapter'){
		return get_post_meta($post_ID,'chapter_order',true);
	}
	return $slug;
}
//add_filter('wp_unique_post_slug','chapter_post_name',99,6);
//Rewrite term links from front-end
add_filter('term_link', 'term_link_filter', 10, 3);
function term_link_filter( $url, $term, $taxonomy ) {
	$url = get_site_url() . '/read?' . $taxonomy . '_included=' . $term -> term_id;
    return $url;  
}
//Filter Message Link
function messages_link($url,$post){
	if ($post->post_type != 'message'){
		return $url;
	}
	return get_home_url() . '/dashboard/chat';
}
add_filter('post_type_link','messages_link',11,2);
//Filter Chapter Link
function my_permalinks($permalink, $post, $leavename) {
	$post_id = $post->ID;
	if ($post->post_type === 'book'){
		return get_home_url() . '/story/' . $post_id;
	}
	else if($post->post_type === 'chapter'){
		return get_permalink($post->post_parent) . '/' . get_post_meta($post->ID,'chapter_order',true);
	}
	return $permalink;
}
add_filter('post_type_link', 'my_permalinks', 11, 3);
// // Register Custom Taxonomies
function bw_register_ct(){
	$labels = array(
		'name'                       => 'Genres',
		'singular_name'              => 'Genre',
		'menu_name'                  => 'Genre',
		'all_items'                  => 'All Genres',
		'parent_item'                => 'Parent Genre',
		'parent_item_colon'          => 'Parent genre:',
		'new_item_name'              => 'New Genre',
		'add_new_item'               => 'Add New Genre',
		'edit_item'                  => 'Edit Genre',
		'update_item'                => 'Update Genre',
		'view_item'                  => 'View Genre',
		'separate_items_with_commas' => 'Separate genres with commas',
		'add_or_remove_items'        => 'Add or remove genres',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Genres',
		'search_items'               => 'Search Genre',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Genres',
		'items_list'                 => 'Genre list',
		'items_list_navigation'      => 'Genre list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'genre', array( 'book' ), $args );
	$labels = array(
		'name'                       => 'Ratings',
		'singular_name'              => 'Rating',
		'menu_name'                  => 'Rating',
		'all_items'                  => 'All Ratings',
		'parent_item'                => 'Parent Rating',
		'parent_item_colon'          => 'Parent rating:',
		'new_item_name'              => 'New Rating',
		'add_new_item'               => 'Add New Rating',
		'edit_item'                  => 'Edit Rating',
		'update_item'                => 'Update Rating',
		'view_item'                  => 'View Rating',
		'separate_items_with_commas' => 'Separate ratings with commas',
		'add_or_remove_items'        => 'Add or remove ratings',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Ratings',
		'search_items'               => 'Search Rating',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Ratings',
		'items_list'                 => 'Rating list',
		'items_list_navigation'      => 'Rating list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'rating', array( 'book' ), $args );
	$labels = array(
		'name'                       => 'Languages',
		'singular_name'              => 'Language',
		'menu_name'                  => 'Language',
		'all_items'                  => 'All Languages',
		'parent_item'                => 'Parent Language',
		'parent_item_colon'          => 'Parent language:',
		'new_item_name'              => 'New Language',
		'add_new_item'               => 'Add New Language',
		'edit_item'                  => 'Edit Language',
		'update_item'                => 'Update Language',
		'view_item'                  => 'View Language',
		'separate_items_with_commas' => 'Separate languages with commas',
		'add_or_remove_items'        => 'Add or remove languages',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Languages',
		'search_items'               => 'Search Language',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Languages',
		'items_list'                 => 'Language list',
		'items_list_navigation'      => 'Language list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'language', array( 'book' ), $args );
	$labels = array(
		'name'                       => 'Characters',
		'singular_name'              => 'Character',
		'menu_name'                  => 'Character',
		'all_items'                  => 'All Characters',
		'parent_item'                => 'Parent Character',
		'parent_item_colon'          => 'Parent character:',
		'new_item_name'              => 'New Character',
		'add_new_item'               => 'Add New Character',
		'edit_item'                  => 'Edit Character',
		'update_item'                => 'Update Character',
		'view_item'                  => 'View Character',
		'separate_items_with_commas' => 'Separate characters with commas',
		'add_or_remove_items'        => 'Add or remove characters',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Characters',
		'search_items'               => 'Search Character',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Characters',
		'items_list'                 => 'Character list',
		'items_list_navigation'      => 'Character list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'character', array( 'book' ), $args );
	$labels = array(
		'name'                       => 'Status',
		'singular_name'              => 'Status',
		'menu_name'                  => 'Status',
		'all_items'                  => 'All Status',
		'parent_item'                => 'Parent Status',
		'parent_item_colon'          => 'Parent status:',
		'new_item_name'              => 'New Status',
		'add_new_item'               => 'Add New Status',
		'edit_item'                  => 'Edit Status',
		'update_item'                => 'Update Status',
		'view_item'                  => 'View Status',
		'separate_items_with_commas' => 'Separate status with commas',
		'add_or_remove_items'        => 'Add or remove status',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Status',
		'search_items'               => 'Search Status',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Status',
		'items_list'                 => 'Status list',
		'items_list_navigation'      => 'Status list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'status', array( 'book' ), $args );
		$labels = array(
		'name'                       => 'Tags',
		'singular_name'              => 'Tag',
		'menu_name'                  => 'Tag',
		'all_items'                  => 'All Tags',
		'parent_item'                => 'Parent Tag',
		'parent_item_colon'          => 'Parent tag:',
		'new_item_name'              => 'New Tag',
		'add_new_item'               => 'Add New Tag',
		'edit_item'                  => 'Edit Tag',
		'update_item'                => 'Update Tag',
		'view_item'                  => 'View Tag',
		'separate_items_with_commas' => 'Separate tag with commas',
		'add_or_remove_items'        => 'Add or remove tag',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Tag',
		'search_items'               => 'Search Tag',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No Tags',
		'items_list'                 => 'Tag list',
		'items_list_navigation'      => 'Tag list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'tag', array( 'book' ), $args );
	$args = array(
		'hierarchical'               => false,
		'public'                     => false,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
	);
	register_taxonomy( 'message_status', array( 'message' ), $args );
	$args = array(
		'hierarchical'               => false,
		'public'                     => false,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
	);
	register_taxonomy( 'message_between', array( 'message' ), $args );
}
add_action( 'init', 'bw_register_ct' );


// Register Custom Post Types
function bw_register_cpt() {

	$labels = array(
		'name'                  => 'Books',
		'singular_name'         => 'Book',
		'menu_name'             => 'Books',
		'name_admin_bar'        => 'Books',
		'archives'              => 'Book Archives',
		'attributes'            => 'Book Attributes',
		'parent_item_colon'     => 'Parent Book',
		'all_items'             => 'All Books',
		'add_new_item'          => 'Create Book',
		'add_new'               => 'Add New',
		'new_item'              => 'New Book',
		'edit_item'             => 'Edit Book',
		'update_item'           => 'Update Book',
		'view_item'             => 'View Book',
		'view_items'            => 'View Books',
		'search_items'          => 'Search Book',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into book',
		'uploaded_to_this_item' => 'Uploaded to this book',
		'items_list'            => 'Book list',
		'items_list_navigation' => 'Books list navigation',
		'filter_items_list'     => 'Filter books list',
	);
	$args = array(
		'label'                 => __( 'Book' ),
		'labels'                => $labels,
		'taxonomies'            => array( 'category' ),
		'hierarchical'          => true,
		'public'                => true,
		'menu_icon'             => 'dashicons-book-alt',
		'supports'              => array('title','editor','excerpt'),
		'exclude_from_search'   => false
	);
	register_post_type( 'book', $args );
	$labels = array(
		'name'                  => 'Chapters',
		'singular_name'         => 'Chapter',
		'menu_name'             => 'Chapters',
		'name_admin_bar'        => 'Chapters',
		'archives'              => 'Chapter Archives',
		'attributes'            => 'Chapter Attributes',
		'parent_item_colon'     => 'Parent Chapter',
		'all_items'             => 'All Chapters',
		'add_new_item'          => 'Create Chapter',
		'add_new'               => 'Add New',
		'new_item'              => 'New Chapter',
		'edit_item'             => 'Edit Chapter',
		'update_item'           => 'Update Chapter',
		'view_item'             => 'View Chapter',
		'view_items'            => 'View Chapters',
		'search_items'          => 'Search Chapter',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into chapter',
		'uploaded_to_this_item' => 'Uploaded to this chapter',
		'items_list'            => 'Chapter list',
		'items_list_navigation' => 'Chapters list navigation',
		'filter_items_list'     => 'Filter chapters list',
	);
	$args = array(
		'label'                 => __( 'Chapter' ),
		'labels'                => $labels,
		'hierarchical'          => false,
		'public'                => true,
		'supports'              => array('comments'),
		'menu_icon'             => 'dashicons-edit',
		'exclude_from_search'   => true,
	);
	register_post_type( 'chapter', $args );
	$args = array(
		'label'                 => __( 'Message' ),
		'hierarchical'          => false,
		'public'                => true,
		'exclude_from_search'   => true
	);
	register_post_type( 'message', $args );
}
add_action( 'init', 'bw_register_cpt' );

//Add Meta Boxes for selection
function my_add_meta_boxes() {
	add_meta_box( 'chapter-parent', 'Book', 'chapter_attributes_meta_box', 'chapter', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'my_add_meta_boxes' );
function chapter_attributes_meta_box( $post ) {
	$post_type_object = get_post_type_object( $post->post_type );
	$pages = wp_dropdown_pages( array( 'post_type' => 'book', 'selected' => $post->post_parent, 'name' => 'parent_id', 'show_option_none' => __( '(No Book)' ), 'sort_column'=> 'menu_order, post_title', 'echo' => 0, 'authors' => get_current_user_id(), 'post_status' => array('publish','draft'),));
	if ( ! empty( $pages ) ) {
		echo $pages;
	}
	else {
		echo 'You have no books, please <a href="/wp-admin/post-new.php?post_type=book">create a book</a> first.';
	}
}
remove_action('template_redirect', 'redirect_canonical');